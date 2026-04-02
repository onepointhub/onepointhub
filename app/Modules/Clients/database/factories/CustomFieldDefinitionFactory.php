<?php

namespace App\Modules\Clients\database\factories;

use App\Modules\Clients\Enums\CustomFieldType;
use App\Modules\Clients\Models\CustomFieldDefinition;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(CustomFieldDefinition::class)]
class CustomFieldDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->words(2, true),
            'type' => CustomFieldType::Text->value,
            'options' => null,
            'sort_order' => 0,
        ];
    }

    public function select(): static
    {
        return $this->state([
            'type' => CustomFieldType::Select->value,
            'options' => ['Option A', 'Option B', 'Option C'],
        ]);
    }
}
