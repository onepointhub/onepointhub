<?php

namespace Database\Factories;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'type' => fake()->randomElement(ClientType::cases())->value,
            'status' => ClientStatus::Active->value,
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP']),
            'website' => fake()->optional()->url(),
            'vat_number' => fake()->optional()->numerify('GB#########'),
            'notes' => fake()->optional()->sentence(),
            'settings' => null,
        ];
    }

    public function archived(): static
    {
        return $this->state(['status' => ClientStatus::Archived->value]);
    }

    public function individual(): static
    {
        return $this->state(['type' => ClientType::Individual->value]);
    }

    public function company(): static
    {
        return $this->state(['type' => ClientType::Company->value]);
    }
}
