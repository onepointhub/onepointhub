<?php

namespace App\Modules\Projects\Models;

use App\Modules\Core\Models\User;
use App\Modules\Projects\database\factories\TaskCommentFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable $created_at
 */
#[Fillable(['task_id', 'user_id', 'body'])]
#[UseFactory(TaskCommentFactory::class)]
class TaskComment extends Model
{
    /** @use HasFactory<TaskCommentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<TaskCommentReaction, $this>
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(TaskCommentReaction::class);
    }
}
