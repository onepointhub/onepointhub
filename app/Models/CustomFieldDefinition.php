<?php

namespace App\Models;

use App\Enums\CustomFieldType;
use Database\Factories\CustomFieldDefinitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CustomFieldType $type
 */
#[Fillable(['workspace_id', 'label', 'type', 'options', 'sort_order'])]
class CustomFieldDefinition extends Model
{
    /** @use HasFactory<CustomFieldDefinitionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CustomFieldType::class,
            'options' => 'array',
        ];
    }

    /**
     * @return HasMany<CustomFieldValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
