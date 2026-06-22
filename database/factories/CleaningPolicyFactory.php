<?php

namespace Database\Factories;

use App\Models\CleaningPolicy;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleaningPolicy>
 */
class CleaningPolicyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'cleaning_required_after_checkout' => true,
            'cleaning_required_before_checkin' => false,
            'inspection_required_after_cleaning' => false,
            'default_cleaning_duration_minutes' => 120,
            'default_inspection_duration_minutes' => 30,
            'same_day_turnover_min_gap_minutes' => 180,
            'require_before_photos' => false,
            'require_after_photos' => true,
            'require_checklist_completion' => true,
            'require_host_confirmation' => false,
            'auto_create_after_checkout' => true,
            'auto_create_before_checkin' => false,
            'auto_create_after_complaint' => true,
            'auto_create_after_repair' => true,
            'active' => true,
        ];
    }
}
