<?php

namespace Database\Factories;

use App\Enums\JournalEmotion;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'entry_date' => fake()->unique()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'went_well' => fake()->paragraph(),
            'on_my_mind' => fake()->paragraph(),
            'should_intervene' => fake()->paragraph(),
            'emotions' => fake()->randomElements(
                array_column(JournalEmotion::cases(), 'value'),
                fake()->numberBetween(0, 2)
            ),
        ];
    }
}
