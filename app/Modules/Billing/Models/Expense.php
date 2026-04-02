<?php

namespace App\Modules\Billing\Models;

use App\Modules\Billing\database\factories\ExpenseFactory;
use App\Modules\Billing\Enum\ExpenseCategory;
use App\Modules\Clients\Models\Client;
use App\Modules\Core\Models\Concerns\BelongsToWorkspace;
use App\Modules\Core\Models\Concerns\LogsActivity;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'client_id',
    'description',
    'amount',
    'currency',
    'category',
    'receipt_path',
    'billable',
    'invoiced_at',
    'expense_date',
])]
#[UseFactory(ExpenseFactory::class)]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use BelongsToWorkspace, HasFactory, LogsActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'category' => ExpenseCategory::class,
            'billable' => 'boolean',
            'invoiced_at' => 'datetime',
            'expense_date' => 'date',
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
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
