<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceComfortDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceComfortDetail>
 */
class SleepingPlaceComfortDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'mattress_type' => 'foam',
            'mattress_firmness' => 'medium',
            'mattress_thickness_cm' => 18,
            'mattress_condition' => 'good',
            'mattress_newness' => 'normal',
            'mattress_purchase_date' => now()->subYear(),
            'has_mattress_protector' => true,
            'waterproof_mattress_protector' => false,
            'mattress_clean' => true,
            'mattress_has_stains' => false,
            'mattress_has_smell' => false,
            'mattress_sags' => false,
            'has_pillow' => true,
            'pillows_count' => 1,
            'pillow_type' => 'standard',
            'has_blanket' => true,
            'blanket_type' => 'standard',
            'has_extra_blanket' => false,
            'has_bedding' => true,
            'bedding_included' => true,
            'bedding_extra_fee' => 0,
            'bedding_changed_before_guest' => true,
            'has_towel' => false,
            'towel_included' => false,
            'towel_extra_fee' => 0,
            'has_extra_towel' => false,
            'has_bedspread' => true,
            'has_plaid' => false,
            'has_earplugs' => false,
            'has_sleep_mask' => false,
            'has_privacy_curtain' => false,
            'has_personal_lamp' => true,
            'has_socket' => true,
            'has_usb_charger' => false,
            'has_shelf' => true,
            'has_hook' => true,
            'has_phone_place' => false,
            'has_shoe_place' => true,
            'has_luggage_place' => true,
            'privacy_level' => 'moderate',
            'noise_level' => 'moderate',
        ];
    }
}
