<?php

namespace App\Modules\Billing\Models;

use App\Modules\Billing\database\factories\InvoiceFactory;
use App\Modules\Billing\Enum\InvoiceStatus;
use App\Modules\Clients\Models\Client;
use App\Modules\Core\Models\Concerns\BelongsToWorkspace;
use App\Modules\Core\Models\Concerns\LogsActivity;
use App\Modules\Projects\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property InvoiceStatus $status
 * @property CarbonImmutable $due_date
 */
#[Fillable([
    'client_id',
    'project_id',
    'number',
    'status',
    'issue_date',
    'due_date',
    'currency',
    'subtotal',
    'tax_rate',
    'tax_amount',
    'discount_amount',
    'total',
    'notes',
    'terms',
    'paid_at',
    'sent_at',
    'viewed_at',
])]
#[UseFactory(InvoiceFactory::class)]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use BelongsToWorkspace, HasFactory, LogsActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
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
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at');
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balanceDue(): float
    {
        return (float) $this->total - $this->totalPaid();
    }

    public function isOverdue(): bool
    {
        return $this->status !== InvoiceStatus::Paid
            && $this->status !== InvoiceStatus::Void
            && $this->due_date->isPast();
    }
}
