<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Payment;
use App\Models\Student;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_number' => $this->faker->unique()->numerify('PAY-####'),
            'invoice_number' => null,
            'student_id' => Student::factory(),
            'method' => \App\Models\PaymentMethod::factory(),
            'amount' => 100.00,
            'status' => 'pending',
            'receipt_image' => 'receipt.jpg',
        ];
    }
}