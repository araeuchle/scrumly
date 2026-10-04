<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\TeamEventType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamEventType>
 */
class TeamEventTypeFactory extends Factory
{
    protected $model = TeamEventType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->randomElement(['Daily', 'Planning', 'Review', 'Retro']),
            'icon' => 'calendar-days',
            'default_duration_minutes' => 30,
            'default_agenda' => fake()->sentence(),
            'is_recurring' => false,
            'timing' => 'start',
            'track_speaking_time' => false,
            'sort_order' => 0,
        ];
    }

    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
        ]);
    }

    public function trackingSpeakingTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'track_speaking_time' => true,
        ]);
    }

    public function retrospective(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_retrospective' => true,
        ]);
    }
}
