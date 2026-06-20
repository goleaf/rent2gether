<?php

namespace App\Models;

use Database\Factories\SleepingPlaceCompatibilityProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceCompatibilityProfile extends Model
{
    /** @use HasFactory<SleepingPlaceCompatibilityProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'sleeping_place_type',
        'is_top_bunk',
        'is_bottom_bunk',
        'is_sofa',
        'is_floor_mattress',
        'is_for_one_person',
        'is_for_couple',
        'has_curtain',
        'has_locker',
        'locker_has_lock',
        'has_power_socket',
        'has_usb_charger',
        'has_personal_lamp',
        'has_shelf',
        'has_luggage_space',
        'has_bedding',
        'has_towel',
        'privacy_level',
        'noise_level_near_place',
        'light_level_near_place',
        'suitable_for_tall_person',
        'suitable_for_heavy_person',
        'suitable_for_limited_mobility',
        'min_nights',
        'max_nights',
        'can_extend',
        'instant_booking_enabled',
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Compatibility Profile attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'is_top_bunk' => 'boolean',
            'is_bottom_bunk' => 'boolean',
            'is_sofa' => 'boolean',
            'is_floor_mattress' => 'boolean',
            'is_for_one_person' => 'boolean',
            'is_for_couple' => 'boolean',
            'has_curtain' => 'boolean',
            'has_locker' => 'boolean',
            'locker_has_lock' => 'boolean',
            'has_power_socket' => 'boolean',
            'has_usb_charger' => 'boolean',
            'has_personal_lamp' => 'boolean',
            'has_shelf' => 'boolean',
            'has_luggage_space' => 'boolean',
            'has_bedding' => 'boolean',
            'has_towel' => 'boolean',
            'suitable_for_tall_person' => 'boolean',
            'suitable_for_heavy_person' => 'boolean',
            'suitable_for_limited_mobility' => 'boolean',
            'min_nights' => 'integer',
            'max_nights' => 'integer',
            'can_extend' => 'boolean',
            'instant_booking_enabled' => 'boolean',
        ];
    }

    /**
     * Links this Sleeping Place Compatibility Profile to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
