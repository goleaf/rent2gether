<?php

namespace App\Models;

use Database\Factories\PropertyConditionDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyConditionDetail extends Model
{
    /** @use HasFactory<PropertyConditionDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'repair_state',
        'cleanliness_level',
        'smell_level',
        'has_tobacco_smell',
        'has_pet_smell',
        'has_damp_smell',
        'has_kitchen_smell',
        'ventilation_level',
        'has_ventilation',
        'has_kitchen_hood',
        'humidity_level',
        'winter_temperature_level',
        'summer_temperature_level',
        'has_heating',
        'heating_adjustable',
        'has_air_conditioning',
        'has_fan',
        'has_hot_water',
        'has_heating_problems',
        'has_hot_water_problems',
        'indoor_noise_level',
        'street_noise_level',
        'neighbor_noise_level',
        'soundproofing_level',
        'light_level',
        'windows_face_yard',
        'windows_face_street',
        'has_blackout_curtains',
        'has_insects',
        'insects_note',
        'has_mold',
        'mold_note',
        'has_damp_marks',
        'regular_pest_control',
        'furniture_condition',
        'beds_condition',
        'mattresses_condition',
        'wardrobes_condition',
        'tables_condition',
        'chairs_condition',
        'floor_condition',
        'walls_condition',
        'ceiling_condition',
        'windows_condition',
        'doors_condition',
        'locks_condition',
        'plumbing_condition',
        'electricity_condition',
        'kitchen_condition',
        'bathroom_condition',
        'toilet_condition',
        'shower_condition',
        'fridge_condition',
        'stove_condition',
        'washing_machine_condition',
        'last_cleaned_at',
        'last_repaired_at',
        'last_checked_at',
        'last_safety_checked_at',
        'last_plumbing_checked_at',
        'last_electricity_checked_at',
        'last_internet_checked_at',
        'owner_check_note',
    ];

    protected function casts(): array
    {
        return [
            'has_tobacco_smell' => 'boolean',
            'has_pet_smell' => 'boolean',
            'has_damp_smell' => 'boolean',
            'has_kitchen_smell' => 'boolean',
            'has_ventilation' => 'boolean',
            'has_kitchen_hood' => 'boolean',
            'has_heating' => 'boolean',
            'heating_adjustable' => 'boolean',
            'has_air_conditioning' => 'boolean',
            'has_fan' => 'boolean',
            'has_hot_water' => 'boolean',
            'has_heating_problems' => 'boolean',
            'has_hot_water_problems' => 'boolean',
            'windows_face_yard' => 'boolean',
            'windows_face_street' => 'boolean',
            'has_blackout_curtains' => 'boolean',
            'has_insects' => 'boolean',
            'has_mold' => 'boolean',
            'has_damp_marks' => 'boolean',
            'regular_pest_control' => 'boolean',
            'last_cleaned_at' => 'datetime',
            'last_repaired_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'last_safety_checked_at' => 'datetime',
            'last_plumbing_checked_at' => 'datetime',
            'last_electricity_checked_at' => 'datetime',
            'last_internet_checked_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
