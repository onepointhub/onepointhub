<?php

namespace App\Modules\Projects\Models;

use App\Modules\Core\Models\Concerns\BelongsToWorkspace;
use App\Modules\Core\Models\User;
use App\Modules\Projects\database\factories\TimeEntryFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $started_at
 * @property ?CarbonImmutable $ended_at
 * @property ?CarbonImmutable $invoiced_at
 * @property CarbonImmutable $created_at
 */
#[Fillable([
    'project_id',
    'task_id',
    'user_id',
    'description',
    'started_at',
    'ended_at',
    'duration_minutes',
    'billable',
    'hourly_rate',
    'invoiced_at',
])]
#[UseFactory(TimeEntryFactory::class)]
class TimeEntry extends Model
{
    /** @use HasFactory<TimeEntryFactory> */
    use BelongsToWorkspace, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'billable' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'invoiced_at' => 'datetime',
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

    public function isInvoiced(): bool
    {
        return $this->invoiced_at !== null;
    }

    public function isRunning(): bool
    {
        return $this->ended_at === null;
    }
}
