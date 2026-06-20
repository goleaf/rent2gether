<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use App\Models\SavedSearch;
use App\Models\SavedSearchResult;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearchResult>
 */
class SavedSearchResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'saved_search_id' => SavedSearch::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'last_matched_at' => now(),
            'status' => 'matched',
            'match_score' => 85,
            'price_per_night_snapshot' => 25,
            'total_price_snapshot' => 100,
            'current_price_per_night' => 25,
            'current_total_price' => 100,
            'deposit_snapshot' => 0,
            'current_deposit' => 0,
            'price_changed' => false,
            'price_change_amount' => 0,
            'price_change_percent' => 0,
            'became_unavailable' => false,
            'became_available_again' => false,
            'is_new_match' => true,
            'is_notified' => false,
            'notified_at' => null,
        ];
    }
}
