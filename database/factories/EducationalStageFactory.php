<?php

namespace Database\Factories;

use App\Models\EducationalStage;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationalStageFactory extends Factory
{
    protected $model = EducationalStage::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'name_ar' => 'مرحلة '.$this->faker->unique()->numerify('##'),
            'name_en' => 'Stage '.$this->faker->unique()->numerify('##'),
            'sort_order' => $this->faker->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
