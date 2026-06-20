<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceStorageDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceStorageDetail>
 */
class SleepingPlaceStorageDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'has_shoe_space' => true,
            'has_luggage_space' => true,
            'has_backpack_space' => true,
            'has_under_bed_storage' => false,
            'has_under_bed_drawer' => false,
            'has_personal_locker' => true,
            'locker_has_lock' => true,
            'lock_provided' => false,
            'guest_should_bring_lock' => true,
            'can_store_valuables' => true,
            'can_store_documents' => true,
            'can_store_laptop' => true,
            'locker_size' => 'medium',
            'locker_width_cm' => 40,
            'locker_height_cm' => 60,
            'locker_depth_cm' => 45,
            'has_shared_storage_area' => true,
            'can_leave_luggage_before_checkin' => false,
            'can_leave_luggage_after_checkout' => false,
            'storage_responsibility_note' => null,
        ];
    }
}
