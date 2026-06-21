<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInInstruction;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInInstruction>
 */
class BookingCheckInInstructionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'booking_id' => Booking::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'instruction_title' => 'Check-in instructions',
            'public_instruction_text' => 'Arrive during the agreed check-in window.',
            'address_instruction_text' => 'Use the main entrance.',
            'building_entry_instruction' => 'Open the front door carefully.',
            'room_finding_instruction' => 'Follow signs to the room.',
            'sleeping_place_instruction' => 'Your sleeping place is marked on the bed frame.',
            'key_pickup_instruction' => 'Pick up the key from the host.',
            'key_return_instruction' => 'Return the key at checkout.',
            'night_entry_instruction' => 'Use quiet entry after dark.',
            'emergency_instruction' => 'Contact the host if access fails.',
            'exact_address_snapshot' => $this->faker->streetAddress(),
            'room_identifier_snapshot' => 'Room 1',
            'sleeping_place_identifier_snapshot' => 'Bed 1',
            'door_code_encrypted' => '2468',
            'intercom_code_encrypted' => '1357',
            'key_safe_code_encrypted' => '8642',
            'visible_from' => now()->subHour(),
            'visible_until' => now()->addDays(2),
        ];
    }
}
