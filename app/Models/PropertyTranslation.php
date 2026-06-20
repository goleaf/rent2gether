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
        'short_description',
        'summary',
        'full_description',
        'description',
        'location_description',
        'transport_description',
        'neighborhood_description',
        'parking_description',
        'condition_description',
        'access_description',
        'self_check_in_instructions',
        'getting_there',
        'what_guests_like',
        'what_to_know',
        'why_convenient',
        'suitable_for',
        'not_suitable_for',
        'main_pros',
        'important_cons',
        'what_to_know_beforehand',
        'what_is_included',
        'what_is_not_included',
        'what_to_bring',
        'where_to_store_belongings',
        'where_to_store_food',
        'kitchen_instructions',
        'bathroom_instructions',
        'laundry_instructions',
        'key_pickup_instructions',
        'night_entry_instructions',
        'delivery_instructions',
        'guest_visitor_rules_text',
        'courier_rules_text',
        'important_notes',
        'host_contact_instructions',
        'problem_instructions',
        'lost_key_instructions',
        'neighbor_conflict_instructions',
        'repair_problem_instructions',
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
