<?php

namespace App\Modules\Projects\Models;

use App\Modules\Core\Models\Concerns\BelongsToWorkspace;
use App\Modules\Projects\database\factories\TaskLabelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'colour'])]
#[UseFactory(TaskLabelFactory::class)]
class TaskLabel extends Model
{
    /** @use HasFactory<TaskLabelFactory> */
    use BelongsToWorkspace, HasFactory;

    /**
     * @return BelongsToMany<Task, $this>
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_label_task');
    }
}
