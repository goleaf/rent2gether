<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingRelocation;
use App\Models\BookingRelocationInventoryTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationInventoryTransfer>
 */
class BookingRelocationInventoryTransferFactory extends Factory
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
            'booking_id' => Booking::factory(),
            'inventory_item_id' => null,
            'item_name_snapshot' => 'Key',
            'transfer_type' => 'return_old_key',
            'status' => 'pending',
            'from_sleeping_place_id' => null,
            'to_sleeping_place_id' => null,
            'from_room_id' => null,
            'to_room_id' => null,
            'note' => null,
        ];
    }
}
