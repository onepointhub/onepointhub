<?php

namespace App\Modules\Billing\Http\Controllers;

use App\Modules\Billing\Enum\InvoiceStatus;
use App\Modules\Billing\Http\Requests\BulkInvoiceRequest;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\Workspace;
use Illuminate\Http\RedirectResponse;

class InvoiceBulkController extends Controller
{
    public function void(BulkInvoiceRequest $request): RedirectResponse
    {
        $workspace = app(Workspace::class);

        /** @var array<int> $ids */
        $ids = $request->ids;

        foreach ($ids as $id) {
            Invoice::where('id', $id)
                ->where('workspace_id', $workspace->id)
                ->whereNotIn('status', [InvoiceStatus::Void->value])
                ->update(['status' => InvoiceStatus::Void]);
        }

        return back()->with('success', 'Invoices voided.');
    }

    public function markPaid(BulkInvoiceRequest $request): RedirectResponse
    {
        $workspace = app(Workspace::class);

        /** @var array<int> $ids */
        $ids = $request->ids;

        foreach ($ids as $id) {
            Invoice::where('id', $id)
                ->where('workspace_id', $workspace->id)
                ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Void->value])
                ->update(['status' => InvoiceStatus::Paid, 'paid_at' => now()]);
        }

        return back()->with('success', 'Invoices marked as paid.');
    }
}
