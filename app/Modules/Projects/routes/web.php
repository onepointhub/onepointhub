<?php

use App\Modules\Projects\Http\Controllers\ProjectController;
use App\Modules\Projects\Http\Controllers\TaskController;
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
            Route::get('/create', [ProjectController::class, 'create'])->name('create');
            Route::post('/', [ProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
            Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
            Route::patch('/{project}', [ProjectController::class, 'update'])->name('update');
            Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');

            Route::prefix('{project}/tasks')
                ->name('tasks.')
                ->group(function () {
                    Route::post('/', [TaskController::class, 'store'])->name('store');
                    Route::patch('{task}', [TaskController::class, 'update'])->name('update');
                    Route::delete('{task}', [TaskController::class, 'destroy'])->name('destroy');
                });
        });
});
