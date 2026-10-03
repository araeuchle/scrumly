<?php

namespace App\Models;

use Database\Factories\SprintCapacityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sprint_id', 'team_member_id', 'capacity_percent', 'note'])]
class SprintCapacity extends Model
{
    /** @use HasFactory<SprintCapacityFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Sprint, $this>
     */
    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    /**
     * @return BelongsTo<TeamMember, $this>
     */
    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }
}
