<?php

namespace App\Modules\Clients\database\factories;

use App\Modules\Clients\Models\ClientContact;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(ClientContact::class)]
class ClientContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'role' => fake()->optional()->jobTitle(),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(['is_primary' => true]);
    }
}
