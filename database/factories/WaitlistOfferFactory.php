<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaitlistOffer>
 */
class WaitlistOfferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'waitlist_item_id' => WaitlistItem::factory(),
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'booking_id' => null,
            'status' => 'active',
            'offered_at' => now(),
            'offer_expires_at' => now()->addMinutes(30),
            'accepted_at' => null,
            'declined_at' => null,
            'expired_at' => null,
            'skipped_at' => null,
            'current_price_per_night' => 35,
            'current_total_price' => 120,
            'current_deposit' => 20,
            'currency' => 'EUR',
            'hold_started_at' => now(),
            'hold_expires_at' => now()->addMinutes(30),
            'notification_sent_at' => null,
            'guest_response_message' => null,
            'system_note' => null,
        ];
    }

    public function converted(Booking $booking): self
    {
        return $this->state(fn (): array => [
            'booking_id' => $booking->id,
            'status' => 'converted_to_booking',
            'accepted_at' => now(),
        ]);
    }
}
