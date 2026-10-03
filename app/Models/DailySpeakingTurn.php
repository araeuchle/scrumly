<?php

namespace App\Models;

use Database\Factories\DailySpeakingTurnFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sprint_event_id', 'team_member_id', 'seconds', 'started_at'])]
class DailySpeakingTurn extends Model
{
    /** @use HasFactory<DailySpeakingTurnFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SprintEvent, $this>
     */
    public function sprintEvent(): BelongsTo
    {
        return $this->belongsTo(SprintEvent::class);
    }

    /**
     * @return BelongsTo<TeamMember, $this>
     */
    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function isActive(): bool
    {
        return $this->started_at !== null;
    }

    public function currentSeconds(): int
    {
        if ($this->started_at === null) {
            return $this->seconds;
        }

        return $this->seconds + (now()->getTimestamp() - $this->started_at->getTimestamp());
    }
}
