<?php

namespace Database\Factories;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationalLessonFactory extends Factory
{
    protected $model = EducationalLesson::class;

    public function definition(): array
    {
        return [
            'educational_unit_id' => EducationalUnit::factory(),
            'name_ar' => 'درس '.$this->faker->unique()->numerify('##'),
            'name_en' => 'Lesson '.$this->faker->unique()->numerify('##'),
            'is_shared' => false,
            'sort_order' => $this->faker->numberBetween(1, 50),
            'is_active' => true,
        ];
    }
}
