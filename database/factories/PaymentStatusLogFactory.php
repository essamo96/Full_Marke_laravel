<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PaymentStatusLog;
use App\Models\Payment;

class PaymentStatusLogFactory extends Factory
{
    protected $model = PaymentStatusLog::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'action' => 'approved',
            'at' => now(),
        ];
    }
}