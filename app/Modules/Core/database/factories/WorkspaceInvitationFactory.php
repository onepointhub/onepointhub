<?php

namespace App\Modules\Core\database\factories;

use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Models\Workspace;
use App\Modules\Core\Models\WorkspaceInvitation;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

#[UseModel(WorkspaceInvitation::class)]
class WorkspaceInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => WorkspaceRole::Member->value,
            'token' => Str::random(64),
            'expires_at' => now()->addHours(48),
        ];
    }
}
