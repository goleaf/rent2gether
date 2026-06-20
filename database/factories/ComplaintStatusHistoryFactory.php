<?php

namespace Database\Factories;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintStatusHistory>
 */
class ComplaintStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'actor_user_id' => User::factory(),
            'status' => ComplaintStatus::Created->value,
            'note_key' => 'booking.complaint.timeline.created',
            'note' => null,
            'metadata_json' => [],
        ];
    }
}
