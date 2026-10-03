<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Team, $this>
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The team shown as active in the UI: the stored current team if it still belongs to this
     * user, otherwise the user's first team (self-healing the stored value along the way).
     */
    public function resolveCurrentTeam(): ?Team
    {
        if ($this->current_team_id !== null) {
            $current = $this->currentTeam;

            if ($current !== null && $current->isOwnedBy($this)) {
                return $current;
            }
        }

        $fallback = $this->teams()->oldest('id')->first();

        if ($fallback !== null) {
            $this->forceFill(['current_team_id' => $fallback->id])->save();
        }

        return $fallback;
    }
}
