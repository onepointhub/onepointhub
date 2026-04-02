<?php

namespace App\Modules\Projects\database\factories;

use App\Modules\Projects\Models\Milestone;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Milestone::class)]
class MilestoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true).' Milestone',
            'due_at' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(['completed_at' => now()]);
    }
}
