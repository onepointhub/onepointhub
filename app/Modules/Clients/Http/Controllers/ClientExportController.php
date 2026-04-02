<?php

namespace App\Modules\Clients\Http\Controllers;

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\CustomFieldDefinition;
use App\Modules\Clients\Models\CustomFieldValue;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('view-client');

        $workspace = app(Workspace::class);

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"clients-$workspace->slug.csv\"",
        ];

        $callback = function () use ($request) {
            /** @var resource $handle */
            $handle = fopen('php://output', 'w');

            $definitions = CustomFieldDefinition::orderBy('sort_order')->get();

            /** @var array<int|string, string|null> $headerRow */
            $headerRow = array_merge(
                ['ID', 'Name', 'Type', 'Status', 'Currency', 'Website', 'VAT Number', 'Notes', 'Created At'],
                $definitions->pluck('label')->all(),
            );
            fputcsv($handle, $headerRow);

            $query = Client::query()
                ->when($request->input('ids'), function ($q, $ids) {
                    /** @var string $ids */
                    $q->whereIn('id', explode(',', $ids));
                })
                ->when(
                    ! $request->input('ids') && $request->input('status'),
                    fn ($q) => $q->where('status', $request->input('status')),
                )
                ->orderBy('name');

            $clientIds = (clone $query)->pluck('id');
            $allValues = CustomFieldValue::where('model_type', Client::class)
                ->whereIn('model_id', $clientIds)
                ->get()
                ->groupBy('model_id');

            $query->each(function (Client $client) use ($handle, $definitions, $allValues) {
                $values = ($allValues[$client->id] ?? collect())->pluck('value', 'custom_field_definition_id');

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
}
