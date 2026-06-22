<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleaningTask>
 */
class CleaningTaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cleaning_number' => sprintf('CLN-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'cleaning_type' => 'after_check_out',
            'cleaning_scope' => 'sleeping_place',
            'status' => 'scheduled',
            'priority' => 'normal',
            'scheduled_date' => now()->toDateString(),
            'scheduled_start_at' => now()->addHours(2),
            'scheduled_end_at' => now()->addHours(4),
            'responsible_type' => 'not_assigned',
            'access_required' => false,
            'access_confirmed' => false,
            'supplies_required' => false,
            'checklist_completed' => false,
            'before_photos_required' => false,
            'after_photos_required' => true,
            'before_photos_uploaded' => false,
            'after_photos_uploaded' => false,
            'issues_found' => false,
            'damage_found' => false,
            'extra_dirt_found' => false,
            'forgotten_items_found' => false,
            'inventory_issue_found' => false,
            'repair_required' => false,
            'inspection_required' => false,
            'deposit_review_required' => false,
        ];
    }
}
