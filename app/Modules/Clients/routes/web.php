<?php

use App\Modules\Clients\Http\Controllers\ClientBulkController;
use App\Modules\Clients\Http\Controllers\ClientContactController;
use App\Modules\Clients\Http\Controllers\ClientController;
use App\Modules\Clients\Http\Controllers\ClientImportController;
use App\Modules\Clients\Http\Controllers\Portal\PortalAuthController;
use App\Modules\Clients\Http\Controllers\Portal\PortalDashboardController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Clients routes
// ---------------------------------------------------------------------------
Route::middleware('web')->group(function () {
    Route::middleware(['auth', 'verified', 'workspace', 'internal'])
        ->prefix('clients')
        ->name('clients.')
        ->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::get('export', [ClientController::class, 'export'])->name('export');
            Route::get('create', [ClientController::class, 'create'])->name('create');
            Route::post('/', [ClientController::class, 'store'])->name('store');
            Route::get('import', [ClientImportController::class, 'index'])->name('import');
            Route::post('import/upload', [ClientImportController::class, 'upload'])->name('import.upload');
            Route::post('import/execute', [ClientImportController::class, 'execute'])->name('import.execute');
            Route::get('{client}', [ClientController::class, 'show'])->name('show');
            Route::get('{client}/edit', [ClientController::class, 'edit'])->name('edit');
            Route::patch('{client}', [ClientController::class, 'update'])->name('update');

            Route::patch('{client}/archive', [ClientController::class, 'archive'])->name('archive');
            Route::patch('{client}/restore', [ClientController::class, 'restore'])->name('restore')->withTrashed();
            Route::delete('{client}', [ClientController::class, 'destroy'])->name('destroy')->withTrashed();
            Route::post('bulk-archive', [ClientBulkController::class, 'archive'])->name('bulk-archive');
            Route::post('{client}/portal/send-link', [ClientController::class, 'sendPortalLink'])
                ->name('portal.send-link');
            Route::prefix('{client}/contacts')
                ->name('contacts.')
                ->group(function () {
                    Route::post('/', [ClientContactController::class, 'store'])->name('store');
                    Route::patch('{contact}', [ClientContactController::class, 'update'])->name('update');
                    Route::delete('{contact}', [ClientContactController::class, 'destroy'])->name('destroy');
                });
        });
    // ---------------------------------------------------------------------------
    // Client Portal routes (no internal/workspace middleware)
    // ---------------------------------------------------------------------------
    Route::prefix('portal/{workspace_slug}/{client_slug}')
        ->name('portal.')
        ->group(function () {
            Route::get('login', [PortalAuthController::class, 'login'])->name('login');
            Route::get('auth/{token}', [PortalAuthController::class, 'consume'])->name('auth.consume');
            Route::post('logout', [PortalAuthController::class, 'logout'])->name('auth.logout');

            Route::middleware('portal.access')->group(function () {
                Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');
            });
        });
});
