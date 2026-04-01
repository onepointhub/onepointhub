<?php

namespace App\Modules\Projects\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['task_comment_id', 'user_id', 'emoji'])]
class TaskCommentReaction extends Model
{
    /**
     * @return BelongsTo<TaskComment, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
