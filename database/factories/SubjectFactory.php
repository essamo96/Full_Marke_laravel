<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Subject;
use App\Models\Program;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'name_ar' => $this->faker->words(2, true),
            'name_en' => $this->faker->words(2, true),
            'fee' => $this->faker->randomFloat(2, 50, 500),
            'min_payment' => $this->faker->randomFloat(2, 10, 50),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}