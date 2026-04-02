<?php

namespace App\Modules\Projects\database\factories;

use App\Modules\Projects\Enums\BudgetType;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Project::class)]
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Project',
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'type' => fake()->randomElement(ProjectType::cases()),
            'budget' => fake()->optional()->randomFloat(2, 500, 50000),
            'budget_type' => fake()->randomElement(BudgetType::cases()),
            'colour' => fake()->hexColor(),
            'starts_at' => fake()->optional()->dateTimeBetween('-3 months', 'now'),
            'ends_at' => fake()->optional()->dateTimeBetween('now', '+6 months'),
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status' => ProjectStatus::Active,
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => ProjectStatus::Completed,
        ]);
    }
}
