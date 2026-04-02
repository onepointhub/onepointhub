<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Core\Models\User;
use App\Modules\Projects\Enums\TaskPriority;
use App\Modules\Projects\Enums\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'status' => [
                'sometimes',
                'required',
                Rule::enum(TaskStatus::class),
            ],
            'priority' => [
                'sometimes',
                'nullable',
                Rule::enum(TaskPriority::class),
            ],
            'milestone_id' => [
                'sometimes',
                'nullable',
                'exists:milestones,id',
            ],
            'parent_id' => [
                'sometimes',
                'nullable',
                'exists:tasks,id',
            ],
            'assigned_to' => [
                'sometimes',
                'nullable',
                'exists:users,id',
            ],
            'due_at' => [
                'sometimes',
                'nullable',
                'date',
            ],
            'estimated_hours' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],
            'label_ids' => [
                'sometimes',
                'array',
            ],
            'label_ids.*' => [
                'exists:task_labels,id',
            ],
        ];
    }
}
