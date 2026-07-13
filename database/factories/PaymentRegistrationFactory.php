<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PaymentRegistration;
use App\Models\Payment;
use App\Models\Registration;

class PaymentRegistrationFactory extends Factory
{
    protected $model = PaymentRegistration::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'registration_id' => Registration::factory(),
            'allocated_amount' => 100.00,
        ];
    }
}