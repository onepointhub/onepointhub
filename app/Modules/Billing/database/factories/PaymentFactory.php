<?php

namespace App\Modules\Billing\database\factories;

use App\Modules\Billing\Enum\PaymentMethod;
use App\Modules\Billing\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Payment::class)]
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 50, 5000),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => fake()->optional()->bothify('REF-####'),
            'notes' => fake()->optional()->sentence(),
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
