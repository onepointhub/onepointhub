<?php

namespace Database\Factories;

use App\Enums\CustomFieldType;
use App\Models\CustomFieldDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomFieldDefinition>
 */
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
