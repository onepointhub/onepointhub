<?php

namespace App\Modules\Projects\Models;

use App\Modules\Projects\database\factories\MilestoneFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable|null $due_at
 */
#[Fillable(['project_id', 'name', 'due_at', 'completed_at'])]
#[UseFactory(MilestoneFactory::class)]
class Milestone extends Model
{
    /** @use HasFactory<MilestoneFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
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

    //    public function tasks(): HasMany
    //    {
    //        return $this->hasMany(Task::class);
    //    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null
            && $this->completed_at === null
            && $this->due_at->isPast();
    }
}
