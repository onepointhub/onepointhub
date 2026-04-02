<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Core\Models\User;
use App\Modules\Projects\Enums\TaskPriority;
use App\Modules\Projects\Enums\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user->can('update-project');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                Rule::enum(TaskStatus::class),
            ],
            'priority' => [
                'nullable',
                Rule::enum(TaskPriority::class),
            ],
            'milestone_id' => [
                'nullable',
                'exists:milestones,id',
            ],
            'parent_id' => [
                'nullable',
                'exists:tasks,id',
            ],
            'assigned_to' => [
                'nullable',
                'exists:users,id',
            ],
            'due_at' => [
                'nullable',
                'date',
            ],
            'estimated_hours' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'label_ids' => [
                'array',
            ],
            'label_ids.*' => [
                'exists:task_labels,id',
            ],
        ];
    }
}
