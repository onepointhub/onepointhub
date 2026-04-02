<?php

namespace App\Modules\Clients\database\factories;

use App\Modules\Clients\Enums\ClientStatus;
use App\Modules\Clients\Enums\ClientType;
use App\Modules\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

#[UseModel(Client::class)]
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
