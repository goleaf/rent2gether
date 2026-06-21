<?php

namespace Database\Factories;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationAlternative;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCancellationAlternative>
 */
class BookingCancellationAlternativeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_cancellation_id' => BookingCancellation::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'suggestion_type' => 'same_host_place',
            'check_in_date' => now()->addDays(10)->toDateString(),
            'check_out_date' => now()->addDays(12)->toDateString(),
            'price_preview_amount' => 100,
            'currency' => 'EUR',
            'message_key' => 'cancellations.messages.alternative_same_host',
            'sort_order' => 0,
        ];
    }
}
