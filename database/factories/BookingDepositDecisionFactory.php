<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingDepositDecision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingDepositDecision>
 */
class BookingDepositDecisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'deposit_amount' => 30,
            'currency' => 'EUR',
            'decision' => 'return_full',
            'deduction_amount' => 0,
            'return_amount' => 30,
            'reason' => null,
            'evidence_photo_paths_json' => [],
            'guest_comment' => null,
            'host_comment' => null,
            'status' => 'pending_review',
            'decided_at' => null,
            'guest_responded_at' => null,
            'resolved_at' => null,
        ];
    }
}
