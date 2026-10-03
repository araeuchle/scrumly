<?php

namespace Database\Factories;

use App\Enums\ImpedimentPriority;
use App\Enums\ImpedimentStatus;
use App\Models\Impediment;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Impediment>
 */
class ImpedimentFactory extends Factory
{
    protected $model = Impediment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => ImpedimentStatus::Open->value,
            'priority' => ImpedimentPriority::Medium->value,
        ];
    }

    public function escalated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImpedimentStatus::Escalated->value,
            'escalated_at' => now(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImpedimentStatus::Resolved->value,
            'resolved_at' => now(),
        ]);
    }
}
