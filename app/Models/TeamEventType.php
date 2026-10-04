<?php

namespace App\Models;

use Database\Factories\TeamEventTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'team_id',
    'name',
    'icon',
    'default_duration_minutes',
    'default_agenda',
    'is_recurring',
    'timing',
    'track_speaking_time',
    'is_retrospective',
    'sort_order',
])]
class TeamEventType extends Model
{
    /** @use HasFactory<TeamEventTypeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_recurring' => 'boolean',
            'track_speaking_time' => 'boolean',
            'is_retrospective' => 'boolean',
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
    public function sprintEvents(): HasMany
    {
        return $this->hasMany(SprintEvent::class);
    }

    public function timingLabel(): string
    {
        return $this->timing === 'end' ? 'Am Sprint-Ende' : 'Am Sprint-Start';
    }
}
