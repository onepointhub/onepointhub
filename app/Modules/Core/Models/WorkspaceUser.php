<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string $role
 * @property int $user_id
 * @property int $workspace_id
 */
class WorkspaceUser extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
