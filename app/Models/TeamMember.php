<?php

namespace App\Models;

use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'first_name', 'last_name', 'default_capacity_percent'])]
class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory;

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

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
