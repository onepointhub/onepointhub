<?php

namespace Database\Factories;

use App\Models\ClientAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientAddress>
 */
class ClientAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'billing',
            'line1' => fake()->streetAddress(),
            'line2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->optional()->state(),
            'postcode' => fake()->postcode(),
            'country' => fake()->countryCode(),
        ];
    }
}
