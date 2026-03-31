<?php

namespace App\Modules\Projects\Models;

use App\Modules\Clients\Models\Client;
use App\Modules\Core\Models\Concerns\BelongsToWorkspace;
use App\Modules\Core\Models\Concerns\LogsActivity;
use App\Modules\Projects\database\factories\ProjectFactory;
use App\Modules\Projects\Enums\BudgetType;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property ProjectType $type
 * @property ProjectStatus $status
 * @property BudgetType|null $budget_type
 * @property CarbonImmutable|null $starts_at
 * @property CarbonImmutable|null $ends_at
 */
#[Fillable([
    'client_id',
    'name',
    'description',
    'status',
    'type',
    'budget',
    'budget_type',
    'colour',
    'starts_at',
    'ends_at',
])]
#[UseFactory(ProjectFactory::class)]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use BelongsToWorkspace, HasFactory, LogsActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'type' => ProjectType::class,
            'budget_type' => BudgetType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'budget' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<ProjectMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * @return HasMany<Milestone, $this>
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('due_at');
    }

    //    public function tasks(): HasMany
    //    {
    //        return $this->hasMany(Task::class);
    //    }
}
