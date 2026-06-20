<?php

namespace App\Services\SavedSearches;

use App\Models\SavedSearch;
use App\Models\SavedSearchResult;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\Pricing\PricingService;
use Carbon\CarbonImmutable;

class SavedSearchSnapshotService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly AvailabilityService $availability,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function createResultSnapshot(SavedSearch $search, SleepingPlace $place): array
    {
        $price = $this->currentPrice($search, $place);
        $available = $this->currentAvailability($search, $place);
        $now = now();

        return [
            'saved_search_id' => $search->id,
            'sleeping_place_id' => $place->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'first_seen_at' => $now,
            'last_seen_at' => $now,
            'last_matched_at' => $available === false ? null : $now,
            'status' => $available === false ? 'unavailable' : 'matched',
            'price_per_night_snapshot' => $price['per_night'],
            'total_price_snapshot' => $price['total'],
            'current_price_per_night' => $price['per_night'],
            'current_total_price' => $price['total'],
            'deposit_snapshot' => $price['deposit'],
            'current_deposit' => $price['deposit'],
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

    public function refreshResultSnapshot(SavedSearchResult $result): SavedSearchResult
    {
        $result->loadMissing([
            'savedSearch.user:id',
            'sleepingPlace:id,room_id,property_id,status,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency,min_nights,max_nights,max_guests',
            'sleepingPlace.room:id,property_id,status',
            'sleepingPlace.property:id,status',
        ]);

        if (! $result->savedSearch instanceof SavedSearch || ! $result->sleepingPlace instanceof SleepingPlace) {
            return $result;
        }

        $price = $this->currentPrice($result->savedSearch, $result->sleepingPlace);
        $priceChange = $this->detectPriceChange($result, $price);
        $availability = $this->detectAvailabilityChange($result);
        $status = $availability['available'] === false ? 'unavailable' : 'matched';

        $result->forceFill([
            'status' => $status,
            'last_seen_at' => now(),
            'last_matched_at' => $availability['available'] === false ? $result->last_matched_at : now(),
            'current_price_per_night' => $price['per_night'],
            'current_total_price' => $price['total'],
            'current_deposit' => $price['deposit'],
            'price_changed' => $priceChange['changed'],
            'price_change_amount' => $priceChange['amount'],
            'price_change_percent' => $priceChange['percent'],
            'became_unavailable' => $availability['became_unavailable'],
            'became_available_again' => $availability['became_available_again'],
        ])->save();

        return $result->refresh();
    }

    /**
     * @param  array{per_night:float,total:?float,deposit:float,currency:string}  $currentPrice
     * @return array{changed:bool,amount:float,percent:float,state:string}
     */
    public function detectPriceChange(SavedSearchResult $result, array $currentPrice): array
    {
        $previous = $result->total_price_snapshot ?? $result->price_per_night_snapshot;
        $current = $currentPrice['total'] ?? $currentPrice['per_night'];

        if ($previous === null || $current === null) {
            return ['changed' => false, 'amount' => 0.0, 'percent' => 0.0, 'state' => 'unknown'];
        }

        $previousValue = round((float) $previous, 2);
        $currentValue = round((float) $current, 2);
        $amount = round($currentValue - $previousValue, 2);
        $changed = abs($amount) > 0.01;
        $percent = $previousValue > 0 ? round(($amount / $previousValue) * 100, 2) : 0.0;

        return [
            'changed' => $changed,
            'amount' => $amount,
            'percent' => $percent,
            'state' => $amount < -0.01 ? 'dropped' : ($amount > 0.01 ? 'increased' : 'same'),
        ];
    }

    /**
     * @return array{available:?bool,became_unavailable:bool,became_available_again:bool}
     */
    public function detectAvailabilityChange(SavedSearchResult $result): array
    {
        if (! $result->savedSearch instanceof SavedSearch || ! $result->sleepingPlace instanceof SleepingPlace) {
            return ['available' => null, 'became_unavailable' => false, 'became_available_again' => false];
        }

        $available = $this->currentAvailability($result->savedSearch, $result->sleepingPlace);
        $wasUnavailable = $result->status === 'unavailable' || $result->became_unavailable;

        return [
            'available' => $available,
            'became_unavailable' => $available === false,
            'became_available_again' => $available === true && $wasUnavailable,
        ];
    }

    /**
     * @return array{per_night:float,total:?float,deposit:float,currency:string}
     */
    public function currentPrice(SavedSearch $search, SleepingPlace $place): array
    {
        $checkIn = $this->date($search->check_in_date ?: $search->check_in);
        $checkOut = $this->date($search->check_out_date ?: $search->check_out);
        $currency = strtoupper($place->currency ?: $search->currency ?: 'EUR');
        $perNight = round((float) $place->base_price_per_night, 2);

        if (! $checkIn || ! $checkOut || $checkOut->lessThanOrEqualTo($checkIn)) {
            return [
                'per_night' => $perNight,
                'total' => null,
                'deposit' => round((float) ($place->deposit_amount ?? 0), 2),
                'currency' => $currency,
            ];
        }

        $quote = $this->pricing->calculate(
            guest: $search->user ?: new User,
            sleepingPlace: $place,
            checkIn: $checkIn,
            checkOut: $checkOut,
            guestsCount: max(1, (int) $search->guests_count),
        );

        return [
            'per_night' => $perNight,
            'total' => $quote->totalAmount,
            'deposit' => $quote->depositAmount,
            'currency' => $quote->currency,
        ];
    }

    private function currentAvailability(SavedSearch $search, SleepingPlace $place): ?bool
    {
        $checkIn = $this->date($search->check_in_date ?: $search->check_in);
        $checkOut = $this->date($search->check_out_date ?: $search->check_out);

        if (! $checkIn || ! $checkOut || $checkOut->lessThanOrEqualTo($checkIn)) {
            return null;
        }

        return $this->availability->isAvailable($place, $checkIn, $checkOut);
    }

    private function date(mixed $date): ?CarbonImmutable
    {
        if (! $date) {
            return null;
        }

        return CarbonImmutable::parse($date)->startOfDay();
    }
}
