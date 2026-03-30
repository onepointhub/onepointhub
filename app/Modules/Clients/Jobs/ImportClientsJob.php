<?php

namespace App\Modules\Clients\Jobs;

use App\Modules\Clients\Enums\ClientStatus;
use App\Modules\Clients\Enums\ClientType;
use App\Modules\Clients\Models\Client;
use App\Modules\Core\Models\Scopes\WorkspaceScope;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImportClientsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param  array<string, string>  $mapping
     * @param  string  $duplicateHandling  'skip' | 'update' | 'create'
     */
    public function __construct(
        public readonly int $workspaceId,
        public readonly int $userId,
        public readonly string $storagePath,
        public readonly array $mapping,
        public readonly string $duplicateHandling = 'skip',
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        $content = Storage::get($this->storagePath);

        if ($content === null) {
            return;
        }

        app()->instance(Workspace::class, Workspace::findOrFail($this->workspaceId));

        $lines = array_filter(explode("\n", trim($content)));
        $headerLine = array_shift($lines);

        if ($headerLine === null) {
            return;
        }

        $headers = array_map('strval', str_getcsv($headerLine));

        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use ($lines, $headers, &$imported, &$skipped) {
            foreach ($lines as $line) {
                $values = str_getcsv($line);

                if (count($values) !== count($headers)) {
                    continue;
                }

                $row = array_combine($headers, $values);
                $data = $this->mapRow($row);

                if (empty($data['name'])) {
                    continue;
                }

                $existing = Client::withoutGlobalScope(WorkspaceScope::class)
                    ->where('workspace_id', $this->workspaceId)
                    ->where('name', $data['name'])
                    ->first();

                if ($existing !== null) {
                    if ($this->duplicateHandling === 'update') {
                        $existing->update($data);
                    }
                    $skipped++;

                    continue;
                }

                $client = new Client($data);
                $client->save();

                $imported++;
            }
        });

        Storage::delete($this->storagePath);

        // Notify user via database notification
        $user = User::find($this->userId);
        $user?->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'clients.import_complete',
            'data' => [
                'message' => "Import complete: $imported clients imported, $skipped clients skipped.",
            ],
            'read_at' => null,
        ]);
    }

    /**
     * Map a CSV row using the column mapping.
     *
     * @param  array<string, string|null>  $row
     * @return array<string, string|null>
     */
    private function mapRow(array $row): array
    {
        $clientFields = ['name', 'type', 'status', 'currency', 'website', 'vat_number', 'notes'];
        $data = [];

        foreach ($this->mapping as $csvColumn => $clientField) {
            if (in_array($clientField, $clientFields, true) && isset($row[$csvColumn])) {
                $data[$clientField] = $row[$csvColumn] ?: null;
            }
        }

        // Default required enum values
        if (empty($data['type']) || ! in_array($data['type'], array_column(ClientType::cases(), 'value'), true)) {
            $data['type'] = ClientType::Company->value;
        }

        if (empty($data['status']) || ! in_array($data['status'], array_column(ClientStatus::cases(), 'value'), true)) {
            $data['status'] = ClientStatus::Active->value;
        }

        return $data;
    }
}
