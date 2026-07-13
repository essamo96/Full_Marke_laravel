<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EmailVerificationCode;
use App\Models\Student;

class EmailVerificationCodeFactory extends Factory
{
    protected $model = EmailVerificationCode::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'code' => $this->faker->numerify('######'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ];
    }
}