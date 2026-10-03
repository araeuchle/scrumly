<?php

namespace Database\Factories;

use App\Models\DailySpeakingTurn;
use App\Models\SprintEvent;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailySpeakingTurn>
 */
class DailySpeakingTurnFactory extends Factory
{
    protected $model = DailySpeakingTurn::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sprint_event_id' => SprintEvent::factory(),
            'team_member_id' => TeamMember::factory(),
            'seconds' => 0,
            'started_at' => null,
        ];
    }
}
