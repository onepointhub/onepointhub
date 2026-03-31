<?php

use App\Modules\Projects\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Projects routes
// ---------------------------------------------------------------------------
Route::middleware('web')->group(function () {
    Route::middleware(['auth', 'workspace', 'internal'])
        ->prefix('projects')
        ->name('projects.')
        ->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('index');
        });
});
