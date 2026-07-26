<?php

namespace Database\Factories;

use App\Models\BookingRequest;
use App\Models\BookingRequestStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequestStatusLog>
 */
class BookingRequestStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_request_id' => BookingRequest::factory(),
            'user_id' => User::factory(),
            'old_status' => BookingRequest::STATUS_SUBMITTED,
            'new_status' => BookingRequest::STATUS_WAITING_HOST_RESPONSE,
            'reason_key' => 'factory',
            'context_json' => [],
        ];
    }
}
