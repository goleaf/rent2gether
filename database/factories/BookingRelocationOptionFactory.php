<?php

namespace Database\Factories;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationOption;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationOption>
 */
class BookingRelocationOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_relocation_id' => BookingRelocation::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'price_difference_amount' => 0,
            'additional_payment_amount' => 0,
            'refund_amount' => 0,
            'additional_deposit_amount' => 0,
            'currency' => 'EUR',
            'availability_status' => 'available',
            'compatibility_status' => 'good',
            'pricing_status' => 'calculated',
            'distance_label' => null,
            'room_privacy_level' => null,
            'comfort_score' => 80,
            'match_score' => 80,
            'host_note' => null,
            'guest_selected' => false,
            'selected_at' => null,
            'expires_at' => now()->addDay(),
        ];
    }
}
