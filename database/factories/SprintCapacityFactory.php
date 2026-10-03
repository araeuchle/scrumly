<?php

namespace Database\Factories;

use App\Models\Sprint;
use App\Models\SprintCapacity;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SprintCapacity>
 */
class SprintCapacityFactory extends Factory
{
    protected $model = SprintCapacity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sprint_id' => Sprint::factory(),
            'team_member_id' => TeamMember::factory(),
            'capacity_percent' => 100,
            'note' => null,
        ];
    }
}
