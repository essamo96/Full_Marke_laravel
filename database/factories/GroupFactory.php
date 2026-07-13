<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'name' => $this->faker->word,
            'days' => ['Monday', 'Wednesday'],
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'max_capacity' => 20,
            'is_active' => true,
        ];
    }
}