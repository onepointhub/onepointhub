<?php

namespace App\Http\Requests\WorkspaceSettings;

use App\Enums\WorkspaceRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user->can('manage-members');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                Rule::in([
                    WorkspaceRole::Admin->value,
                    WorkspaceRole::Member->value,
                    WorkspaceRole::Client->value,
                ]),
            ],
        ];
    }
}
