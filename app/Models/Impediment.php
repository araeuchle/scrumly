<?php

namespace App\Models;

use App\Enums\ImpedimentPriority;
use App\Enums\ImpedimentStatus;
use Database\Factories\ImpedimentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Team $team
 */
#[Fillable([
    'team_id',
    'sprint_id',
    'title',
    'description',
    'status',
    'priority',
    'reported_by',
    'owner',
    'resolution_notes',
    'escalated_at',
    'resolved_at',
])]
class Impediment extends Model
{
    /** @use HasFactory<ImpedimentFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ImpedimentStatus::class,
            'priority' => ImpedimentPriority::class,
            'escalated_at' => 'datetime',
            'resolved_at' => 'datetime',
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
     * @return BelongsTo<Sprint, $this>
     */
    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    public function escalate(): void
    {
        $this->update([
            'status' => ImpedimentStatus::Escalated,
            'escalated_at' => now(),
        ]);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => ImpedimentStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => ImpedimentStatus::Open,
            'escalated_at' => null,
            'resolved_at' => null,
        ]);
    }
}
