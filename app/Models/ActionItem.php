<?php

namespace App\Models;

use Database\Factories\ActionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Team $team
 */
#[Fillable(['team_id', 'sprint_event_id', 'one_on_one_id', 'description', 'is_done', 'completed_at'])]
class ActionItem extends Model
{
    /** @use HasFactory<ActionItemFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'completed_at' => 'datetime',
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
     * @return BelongsTo<SprintEvent, $this>
     */
    public function sprintEvent(): BelongsTo
    {
        return $this->belongsTo(SprintEvent::class);
    }

    /**
     * @return BelongsTo<OneOnOne, $this>
     */
    public function oneOnOne(): BelongsTo
    {
        return $this->belongsTo(OneOnOne::class);
    }

    public function complete(): void
    {
        $this->update([
            'is_done' => true,
            'completed_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'is_done' => false,
            'completed_at' => null,
        ]);
    }
}
