<?php

namespace Database\Factories;

use App\Models\OneOnOne;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OneOnOne>
 */
class OneOnOneFactory extends Factory
{
    protected $model = OneOnOne::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_member_id' => TeamMember::factory(),
            'held_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->paragraph(),
        ];
    }
}
