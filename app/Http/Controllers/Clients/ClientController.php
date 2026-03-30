<?php

namespace App\Http\Controllers\Clients;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        Gate::authorize('view-client');

        $clients = Client::query()
            ->when(
                $request->input('status'),
                fn ($q, $status) => $q->where('status', $status),
                fn ($q) => $q->where('status', ClientStatus::Active->value),
            )
            ->when(
                $request->input('type'),
                fn ($q, $type) => $q->where('type', $type),
            )
            ->when(
                $request->input('search'),
                fn ($q, $search) => $q->where(function ($q) use ($search) {
                    /** @var string $search */
                    $q->where('name', 'LIKE', "%$search%")
                        ->orWhereHas('contacts', fn ($q) => $q->where('email', 'LIKE', "%$search%"));
                }),
            )
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'slug' => $client->slug,
                'type' => $client->type,
                'status' => $client->status,
                'currency' => $client->currency,
                'created_at' => $client->created_at->toDateString(),
            ]);

        return Inertia::render('clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status', 'type']),
            'statuses' => array_column(ClientStatus::cases(), 'value'),
            'types' => array_column(ClientType::cases(), 'value'),
            'canCreate' => Gate::check('create-client'),
            'canDelete' => Gate::check('delete-client'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('view-client');

        $workspace = app(Workspace::class);

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="clients-'.$workspace->slug.'csv"',
        ];

        $callback = function () use ($request) {
            /** @var resource $handle */
            $handle = fopen('php://output', 'w');

            $definitions = CustomFieldDefinition::orderBy('sort_order')->get();

            /** @var array<int|string, string|null> $data */
            $data = array_merge(
                ['ID', 'Name', 'Type', 'Status', 'Currency', 'Website', 'VAT Number', 'Notes', 'Created At'],
                $definitions->pluck('label')->all(),
            );
            fputcsv($handle, $data);

            Client::query()
                ->when($request->input('ids'), function ($q, $ids) {
                    /** @var string $ids */
                    $q->whereIn('id', explode(',', $ids));
                })
                ->when(
                    ! $request->input('ids') && $request->input('status'),
                    fn ($q) => $q->where('status', $request->input('status')),
                )
                ->orderBy('name')
                ->each(function (Client $client) use ($handle, $definitions) {
                    $values = CustomFieldValue::where('model_type', Client::class)
                        ->where('model_id', $client->id)
                        ->pluck('value', 'custom_field_definition_id');

                    /** @var array<int|string, string|null> $data */
                    $data = array_merge(
                        [
                            $client->id,
                            $client->name,
                            $client->type->value,
                            $client->status->value,
                            $client->currency,
                            $client->website,
                            $client->vat_number,
                            $client->notes,
                            $client->created_at->toDateTimeString(),
                        ],
                        $definitions->map(fn ($d) => $values[$d->id] ?? null)->all(),
                    );

                    fputcsv($handle, $data);
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create(): InertiaResponse
    {
        Gate::authorize('create-client');

        return Inertia::render('clients/Create', [
            'statuses' => array_column(ClientStatus::cases(), 'value'),
            'types' => array_column(ClientType::cases(), 'value'),
            'customFields' => $this->customFieldDefinitions(),
            'customFieldValues' => [],
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::create($request->validated());

        /** @var array<int|string, string|null> $values */
        $values = $request->input('custom_fields', []);

        $this->syncCustomFieldValues($client, $values);

        return redirect()->route('clients.show', $client);
    }

    public function edit(Client $client): InertiaResponse
    {
        Gate::authorize('update-client');

        return Inertia::render('clients/Edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'type' => $client->type->value,
                'status' => $client->status->value,
                'currency' => $client->currency,
                'website' => $client->website,
                'vat_number' => $client->vat_number,
                'notes' => $client->notes,
            ],
            'statuses' => array_column(ClientStatus::cases(), 'value'),
            'types' => array_column(ClientType::cases(), 'value'),
            'customFields' => $this->customFieldDefinitions(),
            'customFieldValues' => $this->customFieldValues($client),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        /** @var array<int|string, string|null> $values */
        $values = $request->input('custom_fields', []);

        $this->syncCustomFieldValues($client, $values);

        return redirect()->route('clients.show', $client);
    }

    public function show(Client $client): InertiaResponse
    {
        Gate::authorize('view-client');

        $contacts = $client->contacts()
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get()
            ->map(fn (ClientContact $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'phone' => $c->phone,
                'role' => $c->role,
                'is_primary' => $c->is_primary,
            ]);

        return Inertia::render('clients/Show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'slug' => $client->slug,
                'type' => $client->type->value,
                'status' => $client->status->value,
                'currency' => $client->currency,
                'website' => $client->website,
                'vat_number' => $client->vat_number,
                'notes' => $client->notes,
                'created_at' => $client->created_at->toDateString(),
            ],
            'contacts' => $contacts,
            'canEdit' => Gate::check('update-client'),
            'canDelete' => Gate::check('delete-client'),
            'canManagePortal' => Gate::check('manage-portal'),
            'customFields' => $this->customFieldDefinitions(),
            'customFieldValues' => $this->customFieldValues($client),
            // Deferred: only loaded when the Activity tab is visited
            'activity' => Inertia::defer(fn () => ActivityLog::query()
                ->where('subject_type', Client::class)
                ->where('subject_id', $client->id)
                ->with('actor')
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'event' => $log->event,
                    'actor' => $log->actor ? [
                        'name' => $log->actor->name,
                        'avatar' => $log->actor->avatar,
                    ] : null,
                    'created_at' => $log->created_at->toISOString(),
                ])
            ),
        ]);
    }

    public function archive(Client $client): RedirectResponse
    {
        Gate::authorize('delete-client');

        $client->update(['status' => ClientStatus::Archived]);

        return redirect()->route('clients.index');
    }

    public function restore(Client $client): RedirectResponse
    {
        Gate::authorize('update-client');

        if ($client->trashed()) {
            $client->restore();
        } else {
            $client->update(['status' => ClientStatus::Active]);
        }

        return redirect()->route('clients.index');
    }

    public function destroy(Client $client): RedirectResponse
    {
        Gate::authorize('delete-client');

        if ($client->trashed()) {
            $client->forceDelete();
        } else {
            $client->delete();
        }

        return redirect()->route('clients.index');
    }

    public function sendPortalLink(Client $client): RedirectResponse
    {
        return app(PortalAuthController::class)->sendLink($client);
    }

    /**
     * @return array<int, array{id: int, label: string, type: string, options: string|null}>
     */
    private function customFieldDefinitions(): array
    {
        return CustomFieldDefinition::orderBy('sort_order')
            ->get()
            ->map(fn (CustomFieldDefinition $field) => [
                'id' => $field->id,
                'label' => $field->label,
                'type' => $field->type->value,
                'options' => $field->options,
            ])
            ->all();
    }

    /**
     * @return array<mixed>
     */
    private function customFieldValues(Client $client): array
    {
        return CustomFieldValue::where('model_type', Client::class)
            ->where('model_id', $client->id)
            ->pluck('value', 'custom_field_definition_id')
            ->all();
    }

    /**
     * @param  array<int|string, string|null>  $values
     */
    private function syncCustomFieldValues(Client $client, array $values): void
    {
        foreach ($values as $definitionId => $value) {
            CustomFieldValue::updateOrCreate(
                [
                    'custom_field_definition_id' => (int) $definitionId,
                    'model_type' => Client::class,
                    'model_id' => $client->id,
                ],
                ['value' => $value],
            );
        }
    }
}
