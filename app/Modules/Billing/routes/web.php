<?php

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
        });
});
