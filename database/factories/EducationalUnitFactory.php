<?php

namespace Database\Factories;

use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationalUnitFactory extends Factory
{
    protected $model = EducationalUnit::class;

    public function definition(): array
    {
        return [
            'educational_stage_id' => EducationalStage::factory(),
            'name_ar' => 'وحدة '.$this->faker->unique()->numerify('##'),
            'name_en' => 'Unit '.$this->faker->unique()->numerify('##'),
            'is_shared' => false,
            'sort_order' => $this->faker->numberBetween(1, 50),
            'is_active' => true,
        ];
    }
}
