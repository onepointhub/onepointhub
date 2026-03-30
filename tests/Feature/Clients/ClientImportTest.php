<?php

use App\Jobs\ImportClientsJob;
use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

it('renders the import page', function () {
    actingAsWorkspaceMember('admin');

    $this->get(route('clients.import'))->assertOk()
        ->assertInertia(fn ($page) => $page->component('clients/Import'));
});

it('uploads a csv and returns headers and preview rows', function () {
    actingAsWorkspaceMember('admin');

    $csv = "name,email,website\nAcme Corp,acme@example.com,https://acme.com\nBeta Ltd,beta@example.com,\n";
    $file = UploadedFile::fake()->createWithContent('clients.csv', $csv);

    $response = $this->postJson(route('clients.import.upload'), ['file' => $file]);

    $response->assertOk()
        ->assertJsonStructure(['path', 'headers', 'preview']);

    expect($response->json('headers'))->toBe(['name', 'email', 'website'])
        ->and($response->json('preview'))->toHaveCount(2);
});

it('rejects non-csv files', function () {
    actingAsWorkspaceMember('admin');

    $file = UploadedFile::fake()->create('clients.pdf', 100, 'application/pdf');

    $this->postJson(route('clients.import.upload'), ['file' => $file])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

it('dispatches the import job', function () {
    Queue::fake();
    actingAsWorkspaceMember('admin');

    $csv = "name,type,status\nAcme Corp,company,active\n";
    $file = UploadedFile::fake()->createWithContent('clients.csv', $csv);
    Storage::put('imports/test.csv', $csv);

    $this->postJson(route('clients.import.execute'), [
        'path' => 'imports/test.csv',
        'mapping' => ['name' => 'name', 'type' => 'type', 'status' => 'status'],
        'duplicate_handling' => 'skip',
    ])->assertOk();

    Queue::assertPushed(ImportClientsJob::class);
});

it('imports clients from csv without duplicates', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);

    $csv = "name,type,status\nAcme Corp,company,active\nBeta Ltd,company,active\n";
    Storage::put('imports/test.csv', $csv);

    $job = new ImportClientsJob(
        workspaceId: $workspace->id,
        userId: auth()->id(),
        storagePath: 'imports/test.csv',
        mapping: ['name' => 'name', 'type' => 'type', 'status' => 'status'],
        duplicateHandling: 'skip',
    );

    $job->handle();

    expect(Client::count())->toBe(2);
});

it('skips duplicate clients when handling is skip', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);

    Client::factory()->create(['name' => 'Acme Corp']);

    $csv = "name,type,status\nAcme Corp,company,active\n";
    Storage::put('imports/test.csv', $csv);

    (new ImportClientsJob(
        workspaceId: $workspace->id,
        userId: auth()->id(),
        storagePath: 'imports/test.csv',
        mapping: ['name' => 'name', 'type' => 'type', 'status' => 'status'],
        duplicateHandling: 'skip',
    ))->handle();

    expect(Client::count())->toBe(1); // not duplicated
});

it('updates duplicate clients when handling is update', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);

    Client::factory()->create(['name' => 'Acme Corp', 'website' => null]);

    $csv = "name,website\nAcme Corp,https://acme.com\n";
    Storage::put('imports/test.csv', $csv);

    (new ImportClientsJob(
        workspaceId: $workspace->id,
        userId: auth()->id(),
        storagePath: 'imports/test.csv',
        mapping: ['name' => 'name', 'website' => 'website'],
        duplicateHandling: 'update',
    ))->handle();

    expect(Client::first()->website)->toBe('https://acme.com');
});

it('imports 500 rows without timeout', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);

    $rows = array_map(fn ($i) => "Client {$i},company,active", range(1, 500));
    $csv = "name,type,status\n".implode("\n", $rows)."\n";
    Storage::put('imports/big.csv', $csv);

    (new ImportClientsJob(
        workspaceId: $workspace->id,
        userId: auth()->id(),
        storagePath: 'imports/big.csv',
        mapping: ['name' => 'name', 'type' => 'type', 'status' => 'status'],
        duplicateHandling: 'skip',
    ))->handle();

    expect(Client::count())->toBe(500);
});
