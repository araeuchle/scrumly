<?php

namespace Database\Factories;

use App\Enums\SprintStatus;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sprint>
 */
class SprintFactory extends Factory
{
    protected $model = Sprint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-1 week', '+1 week');

        return [
            'team_id' => Team::factory(),
            'name' => 'Sprint '.fake()->numberBetween(1, 100),
            'goal' => fake()->sentence(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+2 weeks'),
            'status' => SprintStatus::Planned->value,
        ];
    }
}
