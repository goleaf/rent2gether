<?php

namespace App\Actions\SleepingPlaces;

use App\Enums\SleepingPlaceStatus;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Localization\SupportedContentLocales;
use Illuminate\Support\Facades\DB;

class BulkCreateSleepingPlacesAction
{
    /**
     * @param  array{
     *     count:int,
     *     title_prefix:string,
     *     type:string,
     *     base_price_per_night:float|int|string,
     *     currency:string,
     *     min_nights:int,
     *     max_guests:int
     * }  $data
     */
    public function handle(Room $room, User $host, array $data): int
    {
        $room->loadMissing('property');

        abort_unless($room->property?->isOwnedBy($host), 403);

        return DB::transaction(function () use ($room, $data): int {
            $count = max(1, min(20, (int) $data['count']));
            $existingCount = $room->sleepingPlaces()->count();
            $created = 0;

            for ($index = 1; $index <= $count; $index++) {
                $number = $existingCount + $index;
                $title = trim($data['title_prefix'].' '.$number);

                /** @var SleepingPlace $sleepingPlace */
                $sleepingPlace = $room->sleepingPlaces()->create([
                    'property_id' => $room->property_id,
                    'display_name' => $title,
                    'type' => $data['type'],
                    'status' => SleepingPlaceStatus::Draft->value,
                    'place_number' => (string) $number,
                    'max_guests' => (int) $data['max_guests'],
                    'base_price_per_night' => $data['base_price_per_night'],
                    'currency' => $data['currency'],
                    'min_nights' => (int) $data['min_nights'],
                    'cleaning_fee' => 0,
                    'deposit_amount' => 0,
                    'instant_booking_enabled' => false,
                    'requires_host_approval' => true,
                ]);

                foreach (app(SupportedContentLocales::class)->locales() as $locale) {
                    $sleepingPlace->translations()->create([
                        'locale' => $locale,
                        'title' => $title,
                    ]);
                }

                $created++;
            }

            return $created;
        });
    }
}
