<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Program;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'name_ar' => $this->faker->words(2, true),
            'name_en' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug,
            'type' => $this->faker->randomElement(['primary', 'middle', 'high', 'university', 'general']),
            'short_description' => $this->faker->sentence,
            'long_description' => $this->faker->paragraph,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}