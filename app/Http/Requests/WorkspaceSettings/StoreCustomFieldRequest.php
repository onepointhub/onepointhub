<?php

namespace App\Http\Requests\WorkspaceSettings;

use App\Enums\CustomFieldType;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user->can('manage-workspace');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => [
                'required',
                'string',
                'max:100',
            ],
            'type' => [
                'required',
                Rule::enum(CustomFieldType::class),
            ],
            'options' => [
                'nullable',
                'array',
            ],
            'options.*' => [
                'string',
                'max:100',
            ],
        ];
    }
}
