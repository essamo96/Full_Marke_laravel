<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'full_name_ar' => $this->faker->name,
            'full_name_en' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'guardian_id' => null,
            'phone' => $this->faker->phoneNumber,
            'password' => Hash::make('password'),
            'status' => 1,
        ];
    }
}