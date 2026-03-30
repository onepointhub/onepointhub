<?php

namespace App\Http\Requests\WorkspaceSettings;

use App\Modules\Core\Enums\WorkspaceRole;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteMemberRequest extends FormRequest
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
        $workspace = app(Workspace::class);

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('workspace_invitations')
                    ->where(function ($query) use ($workspace): void {
                        $query->where('workspace_id', $workspace->id)
                            ->whereNull('accepted_at')
                            ->where('expires_at', '>', now());
                    }),
                function (string $attr, mixed $value, Closure $fail) use ($workspace): void {
                    if ($workspace->members()->where('email', $value)->exists()) {
                        $fail('This person is already a workspace member.');
                    }
                },
            ],
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
