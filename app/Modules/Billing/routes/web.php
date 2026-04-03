<?php

use App\Modules\Billing\Http\Controllers\InvoiceBulkController;
use App\Modules\Billing\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Billing routes
// ---------------------------------------------------------------------------
Route::middleware('web')->group(function () {
    Route::middleware(['auth', 'verified', 'workspace', 'internal'])
        ->prefix('billing')
        ->name('billing.')
        ->group(function () {
            Route::get('/', function () {
                return 'Hi';
            })->name('index');
            Route::resource('invoices', InvoiceController::class)->except(['destroy']);
            Route::post('invoices/bulk/void', [InvoiceBulkController::class, 'void'])
                ->name('invoices.bulk.void');
            Route::post('invoices/bulk/mark-paid', [InvoiceBulkController::class, 'markPaid'])
                ->name('invoices.bulk.mark-paid');
        });
});
