<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicFaker = \Faker\Factory::create('ar_SA');

        return [
            'full_name_ar' => $arabicFaker->name(),
            'full_name_en' => fake()->name(),
            'phone' => fake()->unique()->numerify('010#######'),
            'email' => fake()->unique()->safeEmail(),
            'image' => null,
            'date_of_birth' => fake()->dateTimeBetween('-22 years', '-15 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->address(),
            'branch_id' => Branch::factory(),
            'major_profession' => fake()->randomElement(['Science', 'Literature', 'Mathematics', 'Commerce']),
            'health_information' => fake()->boolean(20) ? fake()->sentence() : null,
            'status' => 1,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    /**
     * Indicate that the student is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
