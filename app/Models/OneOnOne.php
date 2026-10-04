<?php

namespace App\Models;

use Database\Factories\OneOnOneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read TeamMember $teamMember
 */
#[Fillable(['team_member_id', 'held_at', 'notes'])]
class OneOnOne extends Model
{
    /** @use HasFactory<OneOnOneFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'held_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<TeamMember, $this>
     */
    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    /**
     * @return HasMany<ActionItem, $this>
     */
    public function actionItems(): HasMany
    {
        return $this->hasMany(ActionItem::class);
    }
}
