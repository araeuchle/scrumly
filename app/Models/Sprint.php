<?php

namespace App\Models;

use App\Enums\SprintStatus;
use Database\Factories\SprintFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Team $team
 */
#[Fillable(['team_id', 'name', 'goal', 'starts_at', 'ends_at', 'status'])]
class Sprint extends Model
{
    /** @use HasFactory<SprintFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'status' => SprintStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return HasMany<SprintEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(SprintEvent::class);
    }

    /**
     * @return HasMany<SprintCapacity, $this>
     */
    public function capacities(): HasMany
    {
        return $this->hasMany(SprintCapacity::class);
    }
}
