<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Projects\Enums\BudgetType;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProjectRequestRules
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function projectRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'type' => ['required', Rule::enum(ProjectType::class)],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'budget_type' => ['nullable', Rule::enum(BudgetType::class)],
            'colour' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'members' => ['array'],
            'members.*.user_id' => ['required', 'exists:users,id'],
            'members.*.role' => ['required', Rule::in(['lead', 'member'])],
            'members.*.hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
