<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\Favorite;
use App\Models\FavoriteCollection;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Favorite>
 */
class FavoriteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'favorite_collection_id' => null,
            'property_id' => null,
            'room_id' => null,
            'bed_id' => Bed::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'source' => 'factory',
            'collection' => 'default',
            'note' => null,
            'personal_note' => null,
            'short_label' => null,
            'label_color' => null,
            'priority' => 0,
            'decision_status' => 'saved',
            'price_at_save' => $this->faker->randomFloat(2, 10, 50),
            'check_in' => now()->addWeek()->toDateString(),
            'check_out' => now()->addDays(10)->toDateString(),
            'check_in_date' => now()->addWeek()->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'nights_count' => 3,
            'guests_count' => 1,
            'currency' => 'EUR',
            'price_per_night_snapshot' => $this->faker->randomFloat(2, 10, 50),
            'total_price_snapshot' => $this->faker->randomFloat(2, 80, 200),
            'deposit_snapshot' => 0,
            'discount_snapshot' => 0,
            'current_price_per_night' => null,
            'current_total_price' => null,
            'current_deposit' => null,
            'price_changed' => false,
            'price_change_amount' => null,
            'price_change_percent' => null,
            'price_last_checked_at' => null,
            'was_available_when_added' => null,
            'is_currently_available' => null,
            'became_unavailable' => false,
            'became_available_again' => false,
            'partial_availability' => false,
            'nearest_available_dates_json' => null,
            'availability_last_checked_at' => null,
            'remind_at' => null,
            'reminder_text' => null,
            'reminder_sent_at' => null,
            'notify_available' => false,
            'notify_price_drop' => false,
            'notify_price_increase' => false,
            'notify_available_again' => true,
            'notify_unavailable' => true,
            'last_viewed_at' => null,
            'added_at' => now(),
        ];
    }

    public function forCollection(FavoriteCollection $collection): self
    {
        return $this->state(fn (): array => [
            'user_id' => $collection->user_id,
            'favorite_collection_id' => $collection->id,
            'collection' => $collection->slug ?: $collection->title,
        ]);
    }
}
