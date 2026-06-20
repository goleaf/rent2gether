<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\HostCleaningFinding;
use App\Models\HostCleaningTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCleaningFinding>
 */
class HostCleaningFindingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'host_cleaning_task_id' => HostCleaningTask::factory(),
            'booking_id' => Booking::factory(),
            'finding_type' => 'extra_dirty',
            'severity' => 'medium',
            'description' => 'Needs extra attention.',
            'photo_paths_json' => [],
            'needs_host_action' => true,
            'needs_guest_notification' => false,
            'needs_repair' => false,
            'needs_deposit_review' => false,
            'status' => 'open',
            'resolved_at' => null,
        ];
    }
}
