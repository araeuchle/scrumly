<?php

namespace App\Models;

use App\Enums\SprintEventStatus;
use Database\Factories\SprintEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sprint_id', 'team_event_type_id', 'scheduled_date', 'duration_minutes', 'agenda', 'status', 'started_at', 'ended_at'])]
class SprintEvent extends Model
{
    /** @use HasFactory<SprintEventFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'status' => SprintEventStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Sprint, $this>
     */
    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    /**
     * @return BelongsTo<TeamEventType, $this>
     */
    public function teamEventType(): BelongsTo
    {
        return $this->belongsTo(TeamEventType::class);
    }

    /**
     * @return HasMany<DailySpeakingTurn, $this>
     */
    public function speakingTurns(): HasMany
    {
        return $this->hasMany(DailySpeakingTurn::class);
    }

    public function remainingSeconds(): int
    {
        if ($this->started_at === null) {
            return $this->duration_minutes * 60;
        }

        $elapsed = now()->getTimestamp() - $this->started_at->getTimestamp();

        return ($this->duration_minutes * 60) - $elapsed;
    }

    public function isOvertime(): bool
    {
        return $this->status === SprintEventStatus::InProgress && $this->remainingSeconds() < 0;
    }
}
