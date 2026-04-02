<?php

namespace App\Modules\Billing\database\factories;

use App\Modules\Billing\Enum\InvoiceStatus;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Invoice::class)]
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $taxRate = fake()->randomFloat(2, 0, 20);
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $total = $subtotal + $taxAmount;

        return [
            'client_id' => Client::factory(),
            'number' => 'INV-'.date('Y').'-'.str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => fake()->randomElement(InvoiceStatus::cases()),
            'issue_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+60 days'),
            'currency' => 'USD',
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total' => $total,
            'notes' => fake()->optional()->sentence(),
            'terms' => fake()->optional()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => InvoiceStatus::Draft]);
    }

    public function sent(): static
    {
        return $this->state([
            'status' => InvoiceStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state([
            'status' => InvoiceStatus::Paid,
            'sent_at' => now()->subDays(10),
            'paid_at' => now()->subDays(2),
        ]);
    }

    public function overdue(): static
    {
        return $this->state([
            'status' => InvoiceStatus::Overdue,
            'due_date' => now()->subDays(5),
            'sent_at' => now()->subDays(35),
        ]);
    }
}
