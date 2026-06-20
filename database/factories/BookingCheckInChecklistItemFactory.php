<?php

namespace Database\Factories;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInChecklistItem>
 */
class BookingCheckInChecklistItemFactory extends Factory
{
    public function definition(): array
    {
        $itemKey = $this->faker->randomElement(['keys_handed_over', 'room_shown', 'rules_explained']);

        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'item_key' => $itemKey,
            'label_key' => 'check_in.checklist.'.$itemKey,
            'status' => 'pending',
            'required' => $this->faker->boolean(70),
            'completed_by_user_id' => null,
            'completed_at' => null,
            'note' => null,
        ];
    }
}
