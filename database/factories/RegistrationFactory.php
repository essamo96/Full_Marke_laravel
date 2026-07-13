<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Group;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'registration_number' => $this->faker->unique()->numerify('REG-####'),
            'student_id' => Student::factory(),
            'group_id' => Group::factory(),
            'fee_snapshot' => 100.00,
            'amount_paid' => 0.00,
            'status' => 'pending',
        ];
    }
}