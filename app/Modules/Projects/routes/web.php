<?php

use App\Modules\Projects\Http\Controllers\MilestoneController;
use App\Modules\Projects\Http\Controllers\ProjectController;
use App\Modules\Projects\Http\Controllers\ProjectTemplateController;
use App\Modules\Projects\Http\Controllers\TaskBulkController;
use App\Modules\Projects\Http\Controllers\TaskCommentController;
use App\Modules\Projects\Http\Controllers\TaskCommentReactionController;
use App\Modules\Projects\Http\Controllers\TaskController;
use App\Modules\Projects\Http\Controllers\TaskDetailController;
use App\Modules\Projects\Http\Controllers\TaskMoveController;
use App\Modules\Projects\Http\Controllers\TimeEntryController;
use App\Modules\Projects\Http\Controllers\TimeLogController;
use App\Modules\Projects\Http\Controllers\TimerController;
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

            // Static routes MUST be declared before wildcard {project} routes
            Route::get('/templates', [ProjectTemplateController::class, 'index'])->name('templates.index');
            Route::post('/from-template', [ProjectTemplateController::class, 'fromTemplate'])->name('from-template');
            Route::delete('/templates/{template}', [ProjectTemplateController::class, 'destroy'])->name('templates.destroy');

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
            Route::get('/{project}/tasks/{task}/detail', TaskDetailController::class)->name('tasks.detail');

            Route::prefix('{project}/tasks/{task}/comments')
                ->name('tasks.comments.')
                ->group(function () {
                    Route::post('/', [TaskCommentController::class, 'store'])->name('store');
                    Route::patch('{comment}', [TaskCommentController::class, 'update'])->name('update');
                    Route::delete('{comment}', [TaskCommentController::class, 'destroy'])->name('destroy');
                    Route::post('{comment}/react', TaskCommentReactionController::class)->name('react');
                });

            Route::get('/{project}/timelog', TimeLogController::class)->name('timelog');
            Route::post('/{project}/time', [TimeEntryController::class, 'store'])->name('time.store');
            Route::delete('/{project}/time/{entry}', [TimeEntryController::class, 'destroy'])->name('time.destroy');
            Route::post('/{project}/timer/start', [TimerController::class, 'start'])->name('timer.start');
            Route::post('/{project}/timer/stop', [TimerController::class, 'stop'])->name('timer.stop');

            Route::post('/{project}/save-as-template', [ProjectTemplateController::class, 'store'])->name('templates.store');
            Route::get('/{project}/gantt', [ProjectController::class, 'gantt'])->name('gantt');
        });
});
