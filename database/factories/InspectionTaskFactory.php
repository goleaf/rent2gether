<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\InspectionTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InspectionTask>
 */
class InspectionTaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inspection_number' => sprintf('INSP-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'inspection_type' => 'post_cleaning',
            'inspection_scope' => 'sleeping_place',
            'status' => 'scheduled',
            'priority' => 'normal',
            'scheduled_at' => now()->addHours(4),
            'responsible_type' => 'not_assigned',
            'checklist_completed' => false,
            'photos_required' => false,
            'photos_uploaded' => false,
            'passed' => false,
            'issues_found' => false,
            'cleaning_required' => false,
            'repair_required' => false,
            'deposit_review_required' => false,
            'calendar_block_required' => false,
        ];
    }
}
