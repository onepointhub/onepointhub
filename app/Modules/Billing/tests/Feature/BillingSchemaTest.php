<?php

use App\Modules\Billing\Enum\InvoiceStatus;
use App\Modules\Billing\Models\Expense;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Billing\Models\InvoiceItem;
use App\Modules\Billing\Models\Payment;
use App\Modules\Clients\Models\Client;
use App\Modules\Core\Models\Workspace;

it('creates an invoice with factory', function () {
    actingAsWorkspaceMember('admin');

    $invoice = Invoice::factory()->create(['number' => 'INV-2025-0001']);

    expect($invoice->number)->toBe('INV-2025-0001')
        ->and($invoice->workspace_id)->toBe(app(Workspace::class)->id);
});

it('creates invoice items with factory', function () {
    actingAsWorkspaceMember('admin');

    $invoice = Invoice::factory()->create();
    InvoiceItem::factory()->count(3)->create(['invoice_id' => $invoice->id]);

    expect($invoice->items()->count())->toBe(3);
});

it('creates a payment with factory', function () {
    actingAsWorkspaceMember('admin');

    $invoice = Invoice::factory()->create();
    $payment = Payment::factory()->create(['invoice_id' => $invoice->id]);

    expect($invoice->payments()->count())->toBe(1)
        ->and($payment->amount)->toBeFloat();
});

it('creates an expense with factory', function () {
    actingAsWorkspaceMember('admin');

    $expense = Expense::factory()->create();

    expect($expense->workspace_id)->toBe(app(Workspace::class)->id)
        ->and($expense->amount)->toBeFloat();
});

it('scopes invoices to the current workspace', function () {
    actingAsWorkspaceMember('admin');

    Invoice::factory()->count(2)->create();

    [, $otherWorkspace] = workspaceWithUser('admin');

    $client = Client::factory()->create();

    $foreign = new Invoice;
    $foreign->workspace_id = $otherWorkspace->id;
    $foreign->client_id = $client->id;
    $foreign->number = 'INV-OTHER-0001';
    $foreign->status = InvoiceStatus::Draft;
    $foreign->currency = 'USD';
    $foreign->subtotal = 0;
    $foreign->tax_rate = 0;
    $foreign->tax_amount = 0;
    $foreign->total = 0;
    $foreign->issue_date = now();
    $foreign->due_date = now()->addDays(30);
    $foreign->saveQuietly();

    expect(Invoice::count())->toBe(1);
});
