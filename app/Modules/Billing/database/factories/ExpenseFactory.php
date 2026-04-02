<?php

namespace App\Modules\Billing\database\factories;

use App\Modules\Billing\Enum\ExpenseCategory;
use App\Modules\Billing\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Expense::class)]
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(4),
            'amount' => fake()->randomFloat(2, 5, 2000),
            'currency' => 'USD',
            'category' => fake()->randomElement(ExpenseCategory::cases()),
            'receipt_path' => null,
            'billable' => fake()->boolean(60),
            'invoiced_at' => null,
            'expense_date' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    public function billable(): static
    {
        return $this->state(['billable' => true, 'invoiced_at' => null]);
    }

    public function invoiced(): static
    {
        return $this->state(['billable' => true, 'invoiced_at' => now()]);
    }
}
