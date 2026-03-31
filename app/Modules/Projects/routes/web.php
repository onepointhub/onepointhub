<?php

use App\Modules\Projects\Http\Controllers\MilestoneController;
use App\Modules\Projects\Http\Controllers\ProjectController;
use App\Modules\Projects\Http\Controllers\TaskBulkController;
use App\Modules\Projects\Http\Controllers\TaskController;
use App\Modules\Projects\Http\Controllers\TaskMoveController;
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

            Route::prefix('{project}/milestones')
                ->name('milestones.')
                ->group(function () {
                    Route::post('/', [MilestoneController::class, 'store'])->name('store');
                    Route::patch('{milestone}', [MilestoneController::class, 'update'])->name('update');
                    Route::delete('{milestone}', [MilestoneController::class, 'destroy'])->name('destroy');
                    Route::patch('{milestone}/complete', [MilestoneController::class, 'complete'])->name('complete');
                });

            Route::get('/{project}/board', [ProjectController::class, 'board'])->name('board');
            Route::patch('/{project}/tasks/{task}/move', TaskMoveController::class)->name('tasks.move');

            Route::get('/{project}/tasks', [ProjectController::class, 'tasks'])->name('tasks');
            Route::post('/{project}/tasks/bulk', TaskBulkController::class)->name('tasks.bulk');
        });
});
