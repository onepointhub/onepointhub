<?php

namespace App\Modules\Clients\Models;

use App\Modules\Clients\database\factories\CustomFieldValueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['custom_field_definition_id', 'model_type', 'model_id', 'value'])]
#[UseFactory(CustomFieldValueFactory::class)]
class CustomFieldValue extends Model
{
    /** @use HasFactory<CustomFieldValueFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<CustomFieldDefinition, $this>
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'custom_field_definition_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
