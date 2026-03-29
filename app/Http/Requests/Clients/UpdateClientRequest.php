<?php

namespace App\Http\Requests\Clients;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Models\Client;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user->can('update-client');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $workspaceId = app(Workspace::class)->id;

        /** @var Client $client */
        $client = $this->route('client');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clients', 'name')
                    ->where('workspace_id', $workspaceId)
                    ->whereNull('deleted_at')
                    ->ignore($client->id),
            ],
            'type' => [
                'required',
                Rule::enum(ClientType::class),
            ],
            'status' => [
                'required',
                Rule::enum(ClientStatus::class),
            ],
            'currency' => [
                'nullable',
                'string',
                'size:3',
            ],
            'website' => [
                'nullable',
                'url',
                'max:255',
            ],
            'vat_number' => [
                'nullable',
                'string',
                'max:50',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'custom_fields' => [
                'nullable',
                'array',
            ],
            'custom_fields.*' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
