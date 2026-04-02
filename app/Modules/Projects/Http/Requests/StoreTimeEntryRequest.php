<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Core\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimeEntryRequest extends FormRequest
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
            'task_id' => [
                'nullable',
                'exists:tasks,id',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'started_at' => [
                'required',
                'date',
            ],
            'ended_at' => [
                'required',
                'date',
                'after:started_at',
            ],
            'billable' => [
                'boolean',
            ],
            'hourly_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }
}
