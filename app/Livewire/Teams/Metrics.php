<?php

namespace App\Livewire\Teams;

use App\Enums\ImpedimentStatus;
use App\Enums\SprintEventStatus;
use App\Models\DailySpeakingTurn;
use App\Models\Impediment;
use App\Models\Sprint;
use App\Models\SprintEvent;
use App\Models\Team;
use App\Models\TeamEventType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Metrics extends Component
{
    private const RECENT_SPRINTS = 6;

    public Team $team;

    public function mount(Team $team): void
    {
        $this->authorize('view', $team);

        $this->team = $team;
    }

    public function render(): View
    {
        $recentSprints = $this->recentSprints();
        $eventOverruns = $this->eventOverruns();
        $impedimentStats = $this->impedimentStats();

        $lastSprintEntry = $recentSprints === [] ? null : $recentSprints[array_key_last($recentSprints)];
        $lastSprint = $lastSprintEntry['sprint'] ?? null;
        $speakingBalance = $this->speakingBalance($lastSprint instanceof Sprint ? $lastSprint : null);

        return view('livewire.teams.metrics', [
            'recentSprints' => $recentSprints,
            'eventOverruns' => $eventOverruns,
            'impedimentStats' => $impedimentStats,
            'speakingBalance' => $speakingBalance,
            'teamHealth' => $this->teamHealth($eventOverruns, $impedimentStats, $speakingBalance),
        ]);
    }

    /**
     * Velocity-Proxy (keine Story-Points vorhanden): Anteil der Events eines Sprints, die
     * tatsächlich abgeschlossen wurden, plus die durchschnittlich geplante Teamkapazität.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentSprints(): array
    {
        return $this->team->sprints()
            ->latest('starts_at')
            ->take(self::RECENT_SPRINTS)
            ->get()
            ->map(function (Sprint $sprint): array {
                $eventTotal = $sprint->events()->count();
                $eventCompleted = $sprint->events()->where('status', SprintEventStatus::Completed->value)->count();

                $avgCapacityRaw = $sprint->capacities()->avg('capacity_percent');

                return [
                    'sprint' => $sprint,
                    'eventTotal' => $eventTotal,
                    'eventCompleted' => $eventCompleted,
                    'completionRate' => $eventTotal > 0 ? round($eventCompleted / $eventTotal * 100) : null,
                    'avgCapacity' => is_numeric($avgCapacityRaw) ? round((float) $avgCapacityRaw, 1) : null,
                ];
            })
            ->reverse()
            ->values()
            ->all();
    }

    /**
     * Wie oft und wie stark ein Event-Typ seine geplante Dauer überzieht, auf Basis
     * abgeschlossener Termine mit erfasster Start-/Endzeit.
     *
     * @return array<int, array<string, mixed>>
     */
    private function eventOverruns(): array
    {
        return $this->team->eventTypes()->get()
            ->map(function (TeamEventType $eventType): array {
                $events = $eventType->sprintEvents()
                    ->whereNotNull('started_at')
                    ->whereNotNull('ended_at')
                    ->get();

                $total = $events->count();

                if ($total === 0) {
                    return [
                        'eventType' => $eventType,
                        'total' => 0,
                        'overrunCount' => 0,
                        'overrunRate' => null,
                        'avgActualMinutes' => null,
                    ];
                }

                $overrunCount = $events->filter(function (SprintEvent $event): bool {
                    if ($event->started_at === null || $event->ended_at === null) {
                        return false;
                    }

                    return $event->started_at->diffInMinutes($event->ended_at) > $event->duration_minutes;
                })->count();

                $avgActualMinutesRaw = $events->avg(function (SprintEvent $event): float {
                    if ($event->started_at === null || $event->ended_at === null) {
                        return 0.0;
                    }

                    return $event->started_at->diffInMinutes($event->ended_at);
                });

                return [
                    'eventType' => $eventType,
                    'total' => $total,
                    'overrunCount' => $overrunCount,
                    'overrunRate' => round($overrunCount / $total * 100),
                    'avgActualMinutes' => is_numeric($avgActualMinutesRaw) ? round((float) $avgActualMinutesRaw, 1) : null,
                ];
            })
            ->sortByDesc('overrunRate')
            ->values()
            ->all();
    }

    /**
     * @return array{open: int, escalated: int, resolvedCount: int, avgResolutionDays: float|null}
     */
    private function impedimentStats(): array
    {
        $impediments = $this->team->impediments;

        $resolved = $impediments->filter(fn (Impediment $impediment) => $impediment->resolved_at !== null);

        $avgResolutionDaysRaw = $resolved->isNotEmpty()
            ? $resolved->avg(function (Impediment $impediment): float {
                if ($impediment->created_at === null || $impediment->resolved_at === null) {
                    return 0.0;
                }

                return $impediment->created_at->diffInDays($impediment->resolved_at);
            })
            : null;

        return [
            'open' => $impediments->filter(fn (Impediment $impediment) => $impediment->status === ImpedimentStatus::Open)->count(),
            'escalated' => $impediments->filter(fn (Impediment $impediment) => $impediment->status === ImpedimentStatus::Escalated)->count(),
            'resolvedCount' => $resolved->count(),
            'avgResolutionDays' => is_numeric($avgResolutionDaysRaw) ? round((float) $avgResolutionDaysRaw, 1) : null,
        ];
    }

    /**
     * Gesamt-Sprechzeit pro Mitglied über die Redezeit-getrackten Events des letzten Sprints
     * &mdash; zeigt, wie gleichmäßig sich das Daily auf das Team verteilt.
     *
     * @return array<string, mixed>|null
     */
    private function speakingBalance(?Sprint $sprint): ?array
    {
        if ($sprint === null) {
            return null;
        }

        $eventIds = $sprint->events()
            ->whereHas('teamEventType', fn ($query) => $query->where('track_speaking_time', true))
            ->pluck('id');

        if ($eventIds->isEmpty()) {
            return null;
        }

        $turns = DailySpeakingTurn::whereIn('sprint_event_id', $eventIds)
            ->with('teamMember')
            ->get();

        if ($turns->isEmpty()) {
            return null;
        }

        $totals = $turns->groupBy('team_member_id')
            ->map(function (Collection $group): array {
                $first = $group->first();

                return [
                    'teamMember' => $first?->teamMember,
                    'totalSeconds' => (int) $group->sum(fn (DailySpeakingTurn $turn) => $turn->currentSeconds()),
                ];
            })
            ->sortByDesc('totalSeconds')
            ->values()
            ->all();

        $secondsColumn = array_column($totals, 'totalSeconds');
        $maxSeconds = $secondsColumn === [] ? 0 : max($secondsColumn);

        return [
            'sprint' => $sprint,
            'totals' => $totals,
            'maxSeconds' => $maxSeconds,
        ];
    }

    /**
     * Team Health als Proxy-Index aus objektiven Signalen (Impediment-Last, Event-Overruns,
     * Redezeit-Balance) &mdash; ersetzt keine echten Team-Gespräche, sondern zeigt nur Trends,
     * die ein Scrum Master sonst leicht übersieht. Fehlt ein Signal mangels Daten, fließt es
     * nicht in den Durchschnitt ein, statt ihn künstlich zu beschönigen oder zu verschlechtern.
     *
     * @param  array<int, array<string, mixed>>  $eventOverruns
     * @param  array{open: int, escalated: int}  $impedimentStats
     * @param  array<string, mixed>|null  $speakingBalance
     * @return array{score: float, label: string, signals: array<int, array{label: string, score: float|null}>}
     */
    private function teamHealth(array $eventOverruns, array $impedimentStats, ?array $speakingBalance): array
    {
        $overrunRates = array_values(array_filter(
            array_column($eventOverruns, 'overrunRate'),
            fn ($rate) => is_numeric($rate)
        ));
        $overrunScore = $overrunRates === [] ? null : 100 - (array_sum($overrunRates) / count($overrunRates));

        // Jedes offene Impediment kostet 15 Punkte, jedes eskalierte 25 &mdash; eskalierte
        // Blocker wiegen schwerer, weil sie das Team bereits nachweislich länger blockieren.
        $impedimentScore = max(0, 100 - ($impedimentStats['open'] * 15) - ($impedimentStats['escalated'] * 25));

        $balanceScore = null;

        if ($speakingBalance !== null && is_numeric($speakingBalance['maxSeconds']) && $speakingBalance['maxSeconds'] > 0) {
            $totalsArray = is_array($speakingBalance['totals']) ? $speakingBalance['totals'] : [];
            $seconds = array_values(array_filter(array_column($totalsArray, 'totalSeconds'), fn ($value) => is_numeric($value)));

            if ($seconds !== []) {
                $balanceScore = 100 - ((max($seconds) - min($seconds)) / (float) $speakingBalance['maxSeconds'] * 100);
            }
        }

        $signals = [
            ['label' => 'Impediment-Last', 'score' => (float) $impedimentScore],
            ['label' => 'Event-Overruns', 'score' => $overrunScore],
            ['label' => 'Redezeit-Balance', 'score' => $balanceScore],
        ];

        // Impediment-Last ist immer ein gültiger Wert (0 offene/eskalierte Impediments ergibt
        // bereits 100 Punkte), daher enthält $availableScores immer mindestens diesen einen Wert.
        $availableScores = array_values(array_filter(
            array_column($signals, 'score'),
            fn ($score) => $score !== null
        ));

        $score = round(array_sum($availableScores) / count($availableScores), 1);

        $label = match (true) {
            $score >= 80 => 'Gut',
            $score >= 50 => 'Mittel',
            default => 'Kritisch',
        };

        return ['score' => $score, 'label' => $label, 'signals' => $signals];
    }
}
