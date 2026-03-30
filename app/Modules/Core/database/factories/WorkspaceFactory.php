<?php

namespace App\Modules\Core\database\factories;

use App\Modules\Core\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Workspace::class)]
class WorkspaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'plan' => 'self_hosted',
            'currency' => 'USD',
            'settings' => null,
        ];
    }
}
