<?php

namespace App\Models;

use Database\Factories\PropertyTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyTranslation extends Model
{
    /** @use HasFactory<PropertyTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'locale',
        'title',
        'summary',
        'description',
        'neighborhood_description',
        'getting_there',
        'what_guests_like',
        'what_to_know',
        'suitable_for',
        'not_suitable_for',
        'check_in_instructions',
        'check_out_instructions',
        'house_rules_text',
        'safety_notes',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
