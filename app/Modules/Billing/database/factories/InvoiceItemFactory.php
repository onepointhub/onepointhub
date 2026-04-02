<?php

namespace App\Modules\Billing\database\factories;

use App\Modules\Billing\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(InvoiceItem::class)]
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 20);
        $unitPrice = fake()->randomFloat(2, 10, 500);

        return [
            'description' => fake()->sentence(4),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => round($quantity * $unitPrice, 2),
            'tax_rate' => fake()->randomFloat(2, 0, 20),
            'time_entry_ids' => null,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
