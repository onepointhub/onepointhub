<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Jobs\ImportClientsJob;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClientImportController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('create-client');

        return Inertia::render('clients/Import', [
            'clientFields' => ['name', 'type', 'status', 'currency', 'website', 'vat_number', 'notes'],
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        Gate::authorize('create-client');

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:5120', // 5MB
            ],
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            return response()->json(['error' => 'Could not read uploaded file.'], 422);
        }

        $lines = array_filter(explode("\n", trim($content)));

        $headerLine = array_shift($lines);

        if ($headerLine === null) {
            return response()->json(['error' => 'File is empty.'], 422);
        }

        $headers = array_map('strval', str_getcsv($headerLine));

        $preview = [];

        foreach (array_slice($lines, 0, 5) as $line) {
            $row = str_getcsv($line);
            $preview[] = array_combine($headers, array_pad($row, count($headers), ''));
        }

        $path = 'imports/'.Str::uuid().'.csv';
        Storage::put($path, $content);

        return response()->json([
            'path' => $path,
            'headers' => $headers,
            'preview' => $preview,
        ]);
    }

    public function execute(Request $request): JsonResponse
    {
        Gate::authorize('create-client');

        $validated = $request->validate([
            'path' => [
                'required',
                'string',
            ],
            'mapping' => [
                'required',
                'array',
            ],
            'mapping.*' => [
                'nullable',
                'string',
            ],
            'duplicate_handling' => [
                'required',
                'in:skip,update,create',
            ],
        ]);

        $workspace = app(Workspace::class);

        /** @var User $user */
        $user = $request->user();

        ImportClientsJob::dispatch(
            workspaceId: $workspace->id,
            userId: $user->id,
            storagePath: $validated['path'],
            mapping: array_filter($validated['mapping']),
            duplicateHandling: $validated['duplicate_handling']
        );

        return response()->json([
            'message' => 'Import queued. You will be notified when complete.',
        ]);
    }
}
