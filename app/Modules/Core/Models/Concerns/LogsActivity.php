<?php

namespace App\Modules\Core\Models\Concerns;

use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\Workspace;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function (Model $model) use ($event): void {
                if (! app()->bound(Workspace::class)) {
                    return; // Skip in console, seeders, and queue jobs without workspace context.
                }

                /** @var string $id */
                $id = $model->getKey();

                ActivityLog::create([
                    'workspace_id' => app(Workspace::class)->id,
                    'user_id' => auth()->id(),
                    'event' => $event,
                    'subject_type' => get_class($model),
                    'subject_id' => $id,
                    'properties' => $event === 'updated' ? [
                        'old' => collect($model->getOriginal())->except($model->getHidden())->all(),
                        'new' => collect($model->getChanges())->except($model->getHidden())->all(),
                    ] : null,
                ]);
            });
        }
    }
}
