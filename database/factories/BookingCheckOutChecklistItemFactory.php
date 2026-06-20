<?php

namespace Database\Factories;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOutChecklistItem>
 */
class BookingCheckOutChecklistItemFactory extends Factory
{
    public function definition(): array
    {
        $itemKey = $this->faker->randomElement(['keys_returned', 'locker_emptied', 'room_checked']);

        return [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'item_key' => $itemKey,
            'label_key' => 'check_out.checklist.'.$itemKey,
            'status' => 'pending',
            'required' => $this->faker->boolean(70),
            'completed_by_user_id' => null,
            'completed_at' => null,
            'note' => null,
        ];
    }
}
