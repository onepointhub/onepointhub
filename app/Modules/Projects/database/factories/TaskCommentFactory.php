<?php

namespace App\Modules\Projects\database\factories;

use App\Modules\Core\Models\User;
use App\Modules\Projects\Models\TaskComment;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(TaskComment::class)]
class TaskCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
