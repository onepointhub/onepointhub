<?php

namespace App\Modules\Billing\Http\Controllers;

use App\Modules\Billing\Enum\InvoiceStatus;
use App\Modules\Billing\Http\Requests\StoreInvoiceRequest;
use App\Modules\Billing\Http\Requests\UpdateInvoiceRequest;
use App\Modules\Billing\Models\Expense;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Billing\Services\InvoiceNumberService;
use App\Modules\Clients\Models\Client;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\Workspace;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceNumberService $numberService,
    ) {
        //
    }

    public function create(): Response
    {
        $workspace = app(Workspace::class);

        return Inertia::render('Billing::invoices/Create', [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_id']),
            'nextNumber' => $this->numberService->previewNext($workspace),
            'defaultCurrency' => $workspace->setting('currency', 'USD'),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $workspace = app(Workspace::class);
        $validated = $request->validated();

        DB::transaction(function () use ($workspace, $validated) {
            $number = $this->numberService->nextNumber($workspace);
            /** @var array<int, array{description: string, quantity: float|string, unit_price: float|string, tax_rate: float|string, time_entry_ids?: array<int>|null}> $items */
            $items = $validated['items'];
            /** @var float $taxRate */
            $taxRate = $validated['tax_rate'];
            /** @var float $discountAmount */
            $discountAmount = $validated['discount_amount'];
            $totals = $this->calculateTotals(
                $items,
                $taxRate,
                $discountAmount,
            );

            /** @var Invoice $invoice */
            $invoice = Invoice::create([
                'workspace_id' => $workspace->id,
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'] ?? null,
                'number' => $number,
                'status' => InvoiceStatus::Draft,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'currency' => $validated['currency'],
                'subtotal' => $totals['subtotal'],
                'tax_rate' => $validated['tax_rate'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $validated['discount_amount'],
                'total' => $totals['total'],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
            ]);

            $this->syncItems($invoice, $items);
        });

        return redirect()->route('billing.invoices.index')
            ->with('success', 'Invoice created.');
    }

    public function edit(Invoice $invoice): Response
    {
        abort_if(
            in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Void], true),
            403
        );

        return Inertia::render('Billing::invoices/Edit', [
            'invoice' => $invoice->load('items'),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_id']),
            'billableExpenses' => Expense::where('billable', true)
                ->whereNull('invoiced_at')
                ->where(function ($q) use ($invoice) {
                    $q->whereNull('client_id')->orWhere('client_id', $invoice->client_id);
                })
                ->get(['id', 'description', 'amount', 'expense_date']),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validated();
        /** @var array<int, array{description: string, quantity: float|string, unit_price: float|string, tax_rate: float|string, time_entry_ids?: array<int>|null}> $items */
        $items = $validated['items'];
        /** @var float $taxRate */
        $taxRate = $validated['tax_rate'];
        /** @var float $discountAmount */
        $discountAmount = $validated['discount_amount'];
        $totals = $this->calculateTotals(
            $items,
            $taxRate,
            $discountAmount,
        );

        DB::transaction(function () use ($invoice, $validated, $totals, $items) {
            $invoice->update([
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'] ?? null,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'currency' => $validated['currency'],
                'subtotal' => $totals['subtotal'],
                'tax_rate' => $validated['tax_rate'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $validated['discount_amount'],
                'total' => $totals['total'],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
            ]);

            $invoice->items()->delete();
            $this->syncItems($invoice, $items);
        });

        return redirect()->route('billing.invoices.show', $invoice)
            ->with('success', 'Invoice updated.');
    }

    /**
     * @param  array<int, array{description: string, quantity: float|string, unit_price: float|string, tax_rate: float|string, time_entry_ids?: array<int>|null}>  $items
     * @return array{subtotal: float, tax_amount: float, total: float}
     */
    protected function calculateTotals(array $items, float $taxRate, float $discountAmount): array
    {
        $subtotal = collect($items)->sum(fn ($item) => (float) $item['quantity'] * (float) $item['unit_price']);
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $total = $subtotal + $taxAmount - $discountAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => $taxAmount,
            'total' => max(0, round($total, 2)),
        ];
    }

    /**
     * @param  array<int, array{description: string, quantity: float|string, unit_price: float|string, tax_rate: float|string, time_entry_ids?: array<int>|null}>  $items
     */
    protected function syncItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $index => $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => round($quantity * $unitPrice, 2),
                'tax_rate' => (float) $item['tax_rate'],
                'time_entry_ids' => $item['time_entry_ids'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }
}
