<?php

namespace App\Modules\Projects\database\factories;

use App\Modules\Projects\Enums\ProjectMemberRole;
use App\Modules\Projects\Models\ProjectMember;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(ProjectMember::class)]
class ProjectMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role' => fake()->randomElement(ProjectMemberRole::cases()),
            'hourly_rate' => fake()->optional()->randomFloat(2, 20, 200),
        ];
    }

    public function lead(): static
    {
        return $this->state(['role' => ProjectMemberRole::Lead]);
    }
}
