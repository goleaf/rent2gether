<?php

namespace App\Actions\Rooms;

use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Models\Room;
use App\Services\Localization\SupportedContentLocales;

class GenerateSleepingPlaceDraftsAction
{
    public function handle(Room $room): int
    {
        $targetCount = max(0, (int) $room->beds_count);
        $existingCount = $room->sleepingPlaces()->count();
        $created = 0;

        for ($number = $existingCount + 1; $number <= $targetCount; $number++) {
            $title = __('host.sleeping_places.default_name').' '.$number;

            $sleepingPlace = $room->sleepingPlaces()->create([
                'property_id' => $room->property_id,
                'type' => SleepingPlaceType::Single->value,
                'status' => SleepingPlaceStatus::Draft->value,
                'place_number' => (string) $number,
                'display_name' => $title,
                'max_guests' => 1,
                'min_guest_age' => $room->min_guest_age,
                'max_guest_age' => $room->max_guest_age,
                'base_price_per_night' => 0,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                'min_nights' => 1,
                'instant_booking_enabled' => false,
                'requires_host_approval' => true,
            ]);

            foreach (app(SupportedContentLocales::class)->locales() as $locale) {
                $sleepingPlace->translations()->create([
                    'locale' => $locale,
                    'title' => __('host.sleeping_places.default_name', [], $locale).' '.$number,
                ]);
            }

            $created++;
        }

        return $created;
    }
}
