<?php

namespace App\Modules\Projects\database\factories;

use App\Modules\Core\Models\User;
use App\Modules\Projects\Enums\TaskPriority;
use App\Modules\Projects\Enums\TaskStatus;
use App\Modules\Projects\Models\Task;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Task::class)]
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'position' => fake()->numberBetween(0, 100),
            'due_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at' => null,
            'estimated_hours' => fake()->optional()->numberBetween(1, 40),
        ];
    }

    public function done(): static
    {
        return $this->state([
            'status' => TaskStatus::Done,
            'completed_at' => now(),
        ]);
    }

    public function todo(): static
    {
        return $this->state(['status' => TaskStatus::Todo]);
    }
}
