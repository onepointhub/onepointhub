<?php

namespace App\Modules\Projects\Models;

use App\Modules\Core\Models\Concerns\BelongsToWorkspace;
use App\Modules\Projects\database\factories\ProjectTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property array{milestones?: array<int, array{name: string}>, tasks?: array<int, array{title: string, status: string, priority: string, milestone_index?: int}>} $structure
 * @property bool $is_builtin
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
#[Fillable(['name', 'description', 'structure', 'is_builtin'])]
#[UseFactory(ProjectTemplateFactory::class)]
class ProjectTemplate extends Model
{
    /** @use HasFactory<ProjectTemplateFactory> */
    use BelongsToWorkspace, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'structure' => 'array',
            'is_builtin' => 'boolean',
        ];
    }
}
