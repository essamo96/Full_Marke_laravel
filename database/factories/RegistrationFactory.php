<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Group;
use App\Models\Subject;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'registration_number' => $this->faker->unique()->numerify('REG-####'),
            'student_id' => Student::factory(),
            'group_id' => Group::factory(),
            'subject_id' => function (array $attributes) {
                if (! empty($attributes['group_id'])) {
                    $group = Group::find($attributes['group_id']);

                    return $group?->subject_id;
                }

                return Subject::factory();
            },
            'fee_snapshot' => 100.00,
            'amount_paid' => 0.00,
            'status' => 'pending',
        ];
    }
}