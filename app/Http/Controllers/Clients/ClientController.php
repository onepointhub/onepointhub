<?php

namespace App\Http\Controllers\Clients;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Models\Client;
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

            fputcsv($handle, ['ID', 'Name', 'Type', 'Status', 'Currency', 'Website', 'VAT Number', 'Notes', 'Created At']);

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
                ->each(function (Client $client) use ($handle) {
                    fputcsv($handle, [
                        $client->id,
                        $client->name,
                        $client->type->value,
                        $client->status->value,
                        $client->currency,
                        $client->website,
                        $client->vat_number,
                        $client->notes,
                        $client->created_at->toDateTimeString(),
                    ]);
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
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::create($request->validated());

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
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()->route('clients.show', $client);
    }

    public function show(Client $client): InertiaResponse
    {
        Gate::authorize('view-client');

        return Inertia::render('clients/Show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
            ],
        ]);
    }
}
