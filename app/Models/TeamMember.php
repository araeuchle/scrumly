<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property-read Team $team
 */
#[Fillable(['team_id', 'first_name', 'last_name', 'default_capacity_percent'])]
class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory, HasUuids;

    public const ONE_ON_ONE_REMINDER_WEEKS = 4;

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return HasMany<SprintCapacity, $this>
     */
    public function sprintCapacities(): HasMany
    {
        return $this->hasMany(SprintCapacity::class);
    }

    /**
     * @return HasMany<DailySpeakingTurn, $this>
     */
    public function speakingTurns(): HasMany
    {
        return $this->hasMany(DailySpeakingTurn::class);
    }

    /**
     * @return HasMany<OneOnOne, $this>
     */
    public function oneOnOnes(): HasMany
    {
        return $this->hasMany(OneOnOne::class);
    }

    /**
     * @return HasManyThrough<ActionItem, OneOnOne, $this>
     */
    public function actionItems(): HasManyThrough
    {
        return $this->hasManyThrough(ActionItem::class, OneOnOne::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function lastOneOnOneAt(): ?Carbon
    {
        if ($this->relationLoaded('oneOnOnes')) {
            $maxHeldAt = $this->oneOnOnes->max('held_at');

            return $maxHeldAt instanceof Carbon ? $maxHeldAt : null;
        }

        $maxHeldAt = $this->oneOnOnes()->max('held_at');

        return is_string($maxHeldAt) ? Carbon::parse($maxHeldAt) : null;
    }

    /**
     * Ob seit dem letzten 1:1 mehr als ONE_ON_ONE_REMINDER_WEEKS vergangen ist &mdash; oder noch
     * nie eines stattgefunden hat.
     */
    public function needsOneOnOneReminder(): bool
    {
        $lastOneOnOneAt = $this->lastOneOnOneAt();

        if ($lastOneOnOneAt === null) {
            return true;
        }

        return $lastOneOnOneAt->lt(now()->subWeeks(self::ONE_ON_ONE_REMINDER_WEEKS));
    }
}
