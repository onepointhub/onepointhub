<?php

namespace App\Modules\Projects\Models;

use App\Modules\Core\Models\Concerns\BelongsToWorkspace;
use App\Modules\Core\Models\Concerns\LogsActivity;
use App\Modules\Core\Models\User;
use App\Modules\Projects\database\factories\TaskFactory;
use App\Modules\Projects\Enums\TaskPriority;
use App\Modules\Projects\Enums\TaskStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property TaskStatus $status
 * @property TaskPriority $priority
 * @property ?CarbonImmutable $due_at
 */
#[Fillable([
    'project_id',
    'milestone_id',
    'parent_id',
    'assigned_to',
    'created_by',
    'title',
    'description',
    'status',
    'priority',
    'position',
    'due_at',
    'completed_at',
    'estimated_hours',
])]
#[UseFactory(TaskFactory::class)]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use BelongsToWorkspace, HasFactory, LogsActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_at' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<Milestone, $this>
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    /**
     * @return BelongsTo<Task, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function subTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsToMany<TaskLabel, $this>
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(TaskLabel::class, 'task_label_task');
    }
}
