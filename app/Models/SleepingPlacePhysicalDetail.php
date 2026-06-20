<?php

namespace App\Models;

use Database\Factories\SleepingPlacePhysicalDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlacePhysicalDetail extends Model
{
    /** @use HasFactory<SleepingPlacePhysicalDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'place_type',
        'bed_type',
        'length_cm',
        'width_cm',
        'height_cm',
        'height_from_floor_cm',
        'clearance_above_cm',
        'ladder_available',
        'ladder_comfort_level',
        'safety_rail_available',
        'safety_rail_height_cm',
        'max_weight_kg',
        'mattress_type',
        'mattress_firmness',
        'mattress_condition',
        'mattress_age_months',
        'has_mattress_protector',
        'suitable_for_tall_person',
        'suitable_for_tall_guest',
        'suitable_for_heavy_person',
        'suitable_for_heavy_guest',
        'suitable_for_couple',
        'single_guest_only',
        'suitable_for_elderly',
        'suitable_for_limited_mobility',
        'not_suitable_for_limited_mobility',
        'frame_material',
        'frame_stability_level',
        'squeak_level',
    ];

    protected function casts(): array
    {
        return [
            'length_cm' => 'integer',
            'width_cm' => 'integer',
            'height_cm' => 'integer',
            'height_from_floor_cm' => 'integer',
            'clearance_above_cm' => 'integer',
            'ladder_available' => 'boolean',
            'safety_rail_available' => 'boolean',
            'safety_rail_height_cm' => 'integer',
            'max_weight_kg' => 'integer',
            'mattress_age_months' => 'integer',
            'has_mattress_protector' => 'boolean',
            'suitable_for_tall_person' => 'boolean',
            'suitable_for_tall_guest' => 'boolean',
            'suitable_for_heavy_person' => 'boolean',
            'suitable_for_heavy_guest' => 'boolean',
            'suitable_for_couple' => 'boolean',
            'single_guest_only' => 'boolean',
            'suitable_for_elderly' => 'boolean',
            'suitable_for_limited_mobility' => 'boolean',
            'not_suitable_for_limited_mobility' => 'boolean',
        ];
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
