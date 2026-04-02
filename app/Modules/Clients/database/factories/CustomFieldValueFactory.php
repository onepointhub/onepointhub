<?php

namespace App\Modules\Clients\database\factories;

use App\Modules\Clients\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(CustomFieldValue::class)]
class CustomFieldValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'value' => fake()->words(3, true),
        ];
    }
}
