<?php

namespace Database\Factories;

use App\Models\RetroActionItem;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RetroActionItem>
 */
class RetroActionItemFactory extends Factory
{
    protected $model = RetroActionItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'description' => fake()->sentence(),
            'is_done' => false,
        ];
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_done' => true,
            'completed_at' => now(),
        ]);
    }
}
