<?php

namespace Database\Factories;

use App\Enums\SprintEventStatus;
use App\Models\Sprint;
use App\Models\SprintEvent;
use App\Models\TeamEventType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SprintEvent>
 */
class SprintEventFactory extends Factory
{
    protected $model = SprintEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sprint_id' => Sprint::factory(),
            'team_event_type_id' => TeamEventType::factory(),
            'scheduled_date' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'duration_minutes' => 30,
            'agenda' => fake()->sentence(),
            'status' => SprintEventStatus::Scheduled->value,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SprintEventStatus::InProgress->value,
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SprintEventStatus::Completed->value,
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
        ]);
    }
}
