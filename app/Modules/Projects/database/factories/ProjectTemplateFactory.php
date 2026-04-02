<?php

namespace App\Modules\Projects\database\factories;

use App\Modules\Projects\Models\ProjectTemplate;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(ProjectTemplate::class)]
class ProjectTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Template',
            'description' => fake()->optional()->sentence(),
            'structure' => [
                'milestones' => [
                    ['name' => 'Phase 1', 'due_offset_days' => 14],
                ],
                'tasks' => [
                    ['title' => 'Task A', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 0],
                    ['title' => 'Task B', 'status' => 'todo', 'priority' => 'low', 'milestone_index' => null],
                ],
            ],
            'is_builtin' => false,
        ];
    }

    public function builtin(): static
    {
        return $this->state(['is_builtin' => true]);
    }
}
