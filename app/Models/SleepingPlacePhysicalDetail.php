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
        'suitable_for_tall_person',
        'suitable_for_heavy_person',
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
            'suitable_for_tall_person' => 'boolean',
            'suitable_for_heavy_person' => 'boolean',
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
