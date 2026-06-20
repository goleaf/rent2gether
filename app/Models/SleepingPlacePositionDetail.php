<?php

namespace App\Models;

use Database\Factories\SleepingPlacePositionDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlacePositionDetail extends Model
{
    /** @use HasFactory<SleepingPlacePositionDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'privacy_level',
        'has_curtain',
        'curtain_full_cover',
        'curtain_partial_cover',
        'has_partition',
        'has_side_wall',
        'capsule_style',
        'visible_from_door',
        'visible_from_passage',
        'visible_from_other_beds',
        'can_block_light',
        'has_personal_lamp',
        'lamp_adjustable',
        'has_power_socket',
        'power_sockets_count',
        'socket_near_head',
        'socket_near_feet',
        'has_usb_charger',
        'has_usb_c_charger',
        'has_extension_cord',
        'has_shelf',
        'has_hook',
        'has_phone_holder',
        'has_small_table',
        'near_door',
        'near_window',
        'near_radiator',
        'near_air_conditioner',
        'near_power_socket',
        'near_socket',
        'near_passage',
        'near_wardrobe',
        'near_desk',
        'near_balcony',
        'near_bathroom',
        'near_kitchen',
        'in_room_corner',
        'in_room_center',
        'near_wall',
        'between_two_beds',
        'narrow_passage_nearby',
        'noise_level_near_place',
        'light_level_near_place',
        'morning_light',
        'corridor_light_reaches',
        'draft_nearby',
        'top_bunk',
        'bottom_bunk',
        'position_note',
    ];

    protected function casts(): array
    {
        return [
            'has_curtain' => 'boolean',
            'curtain_full_cover' => 'boolean',
            'curtain_partial_cover' => 'boolean',
            'has_partition' => 'boolean',
            'has_side_wall' => 'boolean',
            'capsule_style' => 'boolean',
            'visible_from_door' => 'boolean',
            'visible_from_passage' => 'boolean',
            'visible_from_other_beds' => 'boolean',
            'can_block_light' => 'boolean',
            'has_personal_lamp' => 'boolean',
            'lamp_adjustable' => 'boolean',
            'has_power_socket' => 'boolean',
            'power_sockets_count' => 'integer',
            'socket_near_head' => 'boolean',
            'socket_near_feet' => 'boolean',
            'has_usb_charger' => 'boolean',
            'has_usb_c_charger' => 'boolean',
            'has_extension_cord' => 'boolean',
            'has_shelf' => 'boolean',
            'has_hook' => 'boolean',
            'has_phone_holder' => 'boolean',
            'has_small_table' => 'boolean',
            'near_door' => 'boolean',
            'near_window' => 'boolean',
            'near_radiator' => 'boolean',
            'near_air_conditioner' => 'boolean',
            'near_power_socket' => 'boolean',
            'near_socket' => 'boolean',
            'near_passage' => 'boolean',
            'near_wardrobe' => 'boolean',
            'near_desk' => 'boolean',
            'near_balcony' => 'boolean',
            'near_bathroom' => 'boolean',
            'near_kitchen' => 'boolean',
            'in_room_corner' => 'boolean',
            'in_room_center' => 'boolean',
            'near_wall' => 'boolean',
            'between_two_beds' => 'boolean',
            'narrow_passage_nearby' => 'boolean',
            'morning_light' => 'boolean',
            'corridor_light_reaches' => 'boolean',
            'draft_nearby' => 'boolean',
            'top_bunk' => 'boolean',
            'bottom_bunk' => 'boolean',
        ];
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
