<?php

namespace App\Modules\Projects\database\factories;

use App\Modules\Projects\Models\TaskLabel;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(TaskLabel::class)]
class TaskLabelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'colour' => fake()->hexColor(),
        ];
    }
}
