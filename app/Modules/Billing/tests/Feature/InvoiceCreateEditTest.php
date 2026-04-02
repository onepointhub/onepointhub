<?php

use App\Modules\Billing\Enum\InvoiceStatus;
use App\Modules\Billing\Http\Controllers\InvoiceController;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Clients\Models\Client;
use App\Modules\Core\Models\Workspace;

beforeEach(function () {
    actingAsWorkspaceMember('admin');
});

it('renders the create invoice page', function () {
    $response = $this->get(action([InvoiceController::class, 'create']));

    $response->assertOk()->assertInertia(fn ($page) => $page->component('Billing::invoices/Create'));
});

it('stores a new invoice with line items', function () {
    $workspace = app(Workspace::class);
    $client = Client::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->post(
        action([InvoiceController::class, 'store']),
        [
            'client_id' => $client->id,
            'issue_date' => '2025-01-01',
            'due_date' => '2025-01-31',
            'currency' => 'USD',
            'tax_rate' => 10,
            'items' => [
                ['description' => 'Design work', 'quantity' => 2, 'unit_price' => 500, 'tax_rate' => 10],
            ],
            'notes' => null,
            'terms' => null,
            'discount_amount' => 0,
        ]
    );

    $response->assertRedirect();

    $invoice = Invoice::first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->items()->count())->toBe(1)
        ->and($invoice->subtotal)->toBe('1000.00')
        ->and($invoice->tax_amount)->toBe('100.00')
        ->and($invoice->total)->toBe('1100.00')
        ->and($invoice->status)->toBe(InvoiceStatus::Draft);
});

it('validates required fields on store', function () {
    $response = $this->post(
        action([InvoiceController::class, 'store']),
        []
    );

    $response->assertInvalid(['client_id', 'issue_date', 'due_date', 'currency']);
});

it('renders the edit invoice page', function () {
    $workspace = app(Workspace::class);
    $client = Client::factory()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'status' => InvoiceStatus::Draft,
    ]);

    $response = $this->get(action([InvoiceController::class, 'edit'], $invoice));

    $response->assertOk()->assertInertia(fn ($page) => $page->component('Billing::invoices/Edit'));
});

it('updates an existing invoice', function () {
    $workspace = app(Workspace::class);
    $client = Client::factory()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->draft()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
    ]);

    $response = $this->put(
        action([InvoiceController::class, 'update'], $invoice),
        [
            'client_id' => $client->id,
            'issue_date' => '2025-02-01',
            'due_date' => '2025-03-01',
            'currency' => 'USD',
            'tax_rate' => 0,
            'discount_amount' => 50,
            'items' => [
                ['description' => 'Updated service', 'quantity' => 1, 'unit_price' => 800, 'tax_rate' => 0],
            ],
            'notes' => 'Thank you',
            'terms' => 'Net 30',
        ]
    );

    $response->assertRedirect();

    $invoice->refresh();
    expect($invoice->total)->toBe('750.00')
        ->and($invoice->notes)->toBe('Thank you'); // 800 - 50 discount
});

it('cannot edit a paid invoice', function () {
    $workspace = app(Workspace::class);
    $client = Client::factory()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->paid()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
    ]);

    $response = $this->get(action([InvoiceController::class, 'edit'], $invoice));

    $response->assertForbidden();
});
