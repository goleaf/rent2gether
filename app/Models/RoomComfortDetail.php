<?php

namespace App\Models;

use Database\Factories\RoomComfortDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomComfortDetail extends Model
{
    /** @use HasFactory<RoomComfortDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'has_window',
        'windows_count',
        'view_from_window',
        'sun_side',
        'has_lockable_door',
        'has_room_key',
        'has_wardrobe',
        'has_shared_wardrobe',
        'has_personal_lockers',
        'has_desk',
        'has_chairs',
        'has_mirror',
        'has_heating',
        'heating_adjustable',
        'has_air_conditioning',
        'has_fan',
        'has_humidifier',
        'has_dehumidifier',
        'winter_temperature_level',
        'summer_temperature_level',
        'ventilation_level',
        'can_open_window',
        'can_close_window',
        'has_mosquito_net',
        'has_draft',
        'smell_level',
        'has_damp_smell',
        'has_tobacco_smell',
        'has_pet_smell',
        'light_level',
        'has_main_light',
        'has_night_light',
        'has_personal_lamps',
        'has_curtains',
        'has_blackout_curtains',
        'can_turn_light_at_night',
        'can_work_at_night',
        'can_eat_in_room',
        'can_store_food_in_room',
        'can_use_personal_lamp_at_night',
        'noise_level',
        'street_noise_level',
        'neighbor_noise_level',
        'corridor_noise_level',
        'kitchen_noise_level',
        'bathroom_noise_level',
        'soundproofing_level',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    /**
     * Defines how Laravel converts stored Room Comfort Detail attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'has_window' => 'boolean',
            'windows_count' => 'integer',
            'has_lockable_door' => 'boolean',
            'has_room_key' => 'boolean',
            'has_wardrobe' => 'boolean',
            'has_shared_wardrobe' => 'boolean',
            'has_personal_lockers' => 'boolean',
            'has_desk' => 'boolean',
            'has_chairs' => 'boolean',
            'has_mirror' => 'boolean',
            'has_heating' => 'boolean',
            'heating_adjustable' => 'boolean',
            'has_air_conditioning' => 'boolean',
            'has_fan' => 'boolean',
            'has_humidifier' => 'boolean',
            'has_dehumidifier' => 'boolean',
            'can_open_window' => 'boolean',
            'can_close_window' => 'boolean',
            'has_mosquito_net' => 'boolean',
            'has_draft' => 'boolean',
            'has_damp_smell' => 'boolean',
            'has_tobacco_smell' => 'boolean',
            'has_pet_smell' => 'boolean',
            'has_main_light' => 'boolean',
            'has_night_light' => 'boolean',
            'has_personal_lamps' => 'boolean',
            'has_curtains' => 'boolean',
            'has_blackout_curtains' => 'boolean',
            'can_turn_light_at_night' => 'boolean',
            'can_work_at_night' => 'boolean',
            'can_eat_in_room' => 'boolean',
            'can_store_food_in_room' => 'boolean',
            'can_use_personal_lamp_at_night' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
        ];
    }

    /**
     * Links this Room Comfort Detail to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
