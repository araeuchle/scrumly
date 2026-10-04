<?php

namespace Database\Factories;

use App\Models\Editor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Editor>
 */
class EditorFactory extends Factory
{
    protected $model = Editor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
