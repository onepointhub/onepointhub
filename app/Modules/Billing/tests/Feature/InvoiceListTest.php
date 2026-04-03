<?php

use App\Modules\Billing\Enum\InvoiceStatus;
use App\Modules\Billing\Http\Controllers\InvoiceBulkController;
use App\Modules\Billing\Http\Controllers\InvoiceController;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Clients\Models\Client;
use App\Modules\Core\Models\Workspace;

beforeEach(function () {
    actingAsWorkspaceMember('admin');
});

it('renders the invoice list page', function () {
    $response = $this->get(action([InvoiceController::class, 'index']));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Billing::invoices/Index')
        ->has('invoices')
        ->has('summary')
    );
});

it('lists only invoices from the current workspace', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');
    $client = Client::factory()->create(['workspace_id' => $workspace->id]);

    Invoice::factory()->count(3)->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    [, $otherWorkspace] = workspaceWithUser('admin');
    $otherClient = Client::factory()->create(['workspace_id' => $otherWorkspace->id]);
    Invoice::factory()->create(['workspace_id' => $otherWorkspace->id, 'client_id' => $otherClient->id]);

    // Re-authenticate as original workspace
    app()->instance(Workspace::class, $workspace);
    session(['active_workspace_id' => $workspace->id]);

    setPermissionsTeamId($workspace->id);
    test()->actingAs($user);

    $response = $this->get(action([InvoiceController::class, 'index']));

    $response->assertInertia(fn ($page) => $page->has('invoices.data', 3));
});

it('filters by status', function () {
    $workspace = app(Workspace::class);
    $client = Client::factory()->create(['workspace_id' => $workspace->id]);

    Invoice::factory()->draft()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    Invoice::factory()->sent()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    Invoice::factory()->paid()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);

    $response = $this->get(action([InvoiceController::class, 'index'], ['status' => 'draft']));

    $response->assertInertia(fn ($page) => $page->has('invoices.data', 1));
});

it('returns summary totals in the correct structure', function () {
    $workspace = app(Workspace::class);
    $client = Client::factory()->create(['workspace_id' => $workspace->id]);

    Invoice::factory()->sent()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'total' => 1000]);
    Invoice::factory()->paid()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id, 'total' => 500]);

    $response = $this->get(action([InvoiceController::class, 'index']));

    $response->assertInertia(fn ($page) => $page
        ->has('summary.outstanding')
        ->has('summary.overdue')
        ->has('summary.paid')
    );
});

it('bulk marks invoices as void', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');
    $client = Client::factory()->create(['workspace_id' => $workspace->id]);

    $invoices = Invoice::factory()->draft()->count(2)->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
    ]);

    $response = $this->post(
        action([InvoiceBulkController::class, 'void']),
        ['ids' => $invoices->pluck('id')->toArray()]
    );

    $response->assertRedirect();

    $invoices->each(fn ($i) => expect($i->fresh()->status)->toBe(InvoiceStatus::Void));
});
