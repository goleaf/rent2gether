<?php

namespace App\Models;

use Database\Factories\SleepingPlaceComfortDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceComfortDetail extends Model
{
    /** @use HasFactory<SleepingPlaceComfortDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'mattress_type',
        'mattress_firmness',
        'mattress_thickness_cm',
        'mattress_condition',
        'mattress_newness',
        'mattress_purchase_date',
        'has_mattress_protector',
        'waterproof_mattress_protector',
        'mattress_clean',
        'mattress_has_stains',
        'mattress_has_smell',
        'mattress_sags',
        'has_pillow',
        'pillows_count',
        'pillow_type',
        'has_blanket',
        'blanket_type',
        'has_extra_blanket',
        'has_bedding',
        'bedding_included',
        'bedding_extra_fee',
        'bedding_changed_before_guest',
        'has_towel',
        'towel_included',
        'towel_extra_fee',
        'has_extra_towel',
        'has_bedspread',
        'has_plaid',
        'has_earplugs',
        'has_sleep_mask',
        'has_privacy_curtain',
        'has_personal_lamp',
        'has_socket',
        'has_usb_charger',
        'has_shelf',
        'has_hook',
        'has_phone_place',
        'has_shoe_place',
        'has_luggage_place',
        'privacy_level',
        'noise_level',
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Comfort Detail attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'mattress_thickness_cm' => 'integer',
            'mattress_purchase_date' => 'date',
            'has_mattress_protector' => 'boolean',
            'waterproof_mattress_protector' => 'boolean',
            'mattress_clean' => 'boolean',
            'mattress_has_stains' => 'boolean',
            'mattress_has_smell' => 'boolean',
            'mattress_sags' => 'boolean',
            'has_pillow' => 'boolean',
            'pillows_count' => 'integer',
            'has_blanket' => 'boolean',
            'has_extra_blanket' => 'boolean',
            'has_bedding' => 'boolean',
            'bedding_included' => 'boolean',
            'bedding_extra_fee' => 'decimal:2',
            'bedding_changed_before_guest' => 'boolean',
            'has_towel' => 'boolean',
            'towel_included' => 'boolean',
            'towel_extra_fee' => 'decimal:2',
            'has_extra_towel' => 'boolean',
            'has_bedspread' => 'boolean',
            'has_plaid' => 'boolean',
            'has_earplugs' => 'boolean',
            'has_sleep_mask' => 'boolean',
            'has_privacy_curtain' => 'boolean',
            'has_personal_lamp' => 'boolean',
            'has_socket' => 'boolean',
            'has_usb_charger' => 'boolean',
            'has_shelf' => 'boolean',
            'has_hook' => 'boolean',
            'has_phone_place' => 'boolean',
            'has_shoe_place' => 'boolean',
            'has_luggage_place' => 'boolean',
        ];
    }

    /**
     * Links this Sleeping Place Comfort Detail to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
