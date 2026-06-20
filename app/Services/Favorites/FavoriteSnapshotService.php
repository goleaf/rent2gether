<?php

namespace App\Services\Favorites;

use App\Data\Favorites\FavoriteContext;
use App\Data\Favorites\FavoritePriceChangeResult;
use App\Models\Favorite;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\Pricing\PricingService;
use Carbon\CarbonImmutable;

class FavoriteSnapshotService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly AvailabilityService $availability,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function createSnapshot(SleepingPlace $place, FavoriteContext $context, ?User $guest = null): array
    {
        $place->loadMissing(['room:id,property_id', 'property:id']);

        $checkIn = $context->checkInDate();
        $checkOut = $context->checkOutDate();
        $nights = $this->nights($checkIn, $checkOut);
        $currentPrice = $this->currentPrice($place, $context, $guest);
        $available = $this->currentAvailability($place, $checkIn, $checkOut);
        $now = now();

        return [
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'source' => $context->source,
            'personal_note' => $context->personalNote,
            'note' => $context->personalNote,
            'short_label' => $context->shortLabel,
            'label_color' => $context->labelColor,
            'decision_status' => $context->decisionStatus,
            'check_in' => $checkIn?->toDateString(),
            'check_out' => $checkOut?->toDateString(),
            'check_in_date' => $checkIn?->toDateString(),
            'check_out_date' => $checkOut?->toDateString(),
            'nights_count' => $nights,
            'guests_count' => max(1, $context->guestsCount),
            'currency' => $currentPrice['currency'],
            'price_at_save' => $currentPrice['total'] ?? $currentPrice['per_night'],
            'price_per_night_snapshot' => $currentPrice['per_night'],
            'total_price_snapshot' => $currentPrice['total'],
            'deposit_snapshot' => $currentPrice['deposit'],
            'discount_snapshot' => $currentPrice['discount'],
            'current_price_per_night' => $currentPrice['per_night'],
            'current_total_price' => $currentPrice['total'],
            'current_deposit' => $currentPrice['deposit'],
            'price_changed' => false,
            'price_change_amount' => 0,
            'price_change_percent' => 0,
            'price_last_checked_at' => $now,
            'was_available_when_added' => $available,
            'is_currently_available' => $available,
            'became_unavailable' => false,
            'became_available_again' => false,
            'partial_availability' => false,
            'nearest_available_dates_json' => null,
            'availability_last_checked_at' => $available === null ? null : $now,
            'notify_available' => $context->notifyAvailableAgain,
            'notify_price_drop' => $context->notifyPriceDrop,
            'notify_price_increase' => $context->notifyPriceIncrease,
            'notify_available_again' => $context->notifyAvailableAgain,
            'notify_unavailable' => $context->notifyUnavailable,
            'added_at' => $now,
        ];
    }

    public function refresh(Favorite $favorite): Favorite
    {
        $favorite->loadMissing(['user:id', 'sleepingPlace:id,room_id,property_id,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency']);

        if (! $favorite->sleepingPlace instanceof SleepingPlace) {
            return $favorite;
        }

        $context = new FavoriteContext(
            checkIn: $favorite->check_in_date ?: $favorite->check_in,
            checkOut: $favorite->check_out_date ?: $favorite->check_out,
            guestsCount: max(1, (int) $favorite->guests_count),
        );
        $currentPrice = $this->currentPrice($favorite->sleepingPlace, $context, $favorite->user);
        $priceChange = $this->detectPriceChange($favorite, $currentPrice);

        $favorite->forceFill([
            'currency' => $currentPrice['currency'],
            'current_price_per_night' => $currentPrice['per_night'],
            'current_total_price' => $currentPrice['total'],
            'current_deposit' => $currentPrice['deposit'],
            'price_changed' => $priceChange->changed,
            'price_change_amount' => $priceChange->amount,
            'price_change_percent' => $priceChange->percent,
            'price_last_checked_at' => now(),
        ])->save();

        return $favorite->refresh();
    }

    /**
     * @param  array{per_night:float,total:?float,deposit:float,discount:float,currency:string}  $currentPrice
     */
    public function detectPriceChange(Favorite $favorite, array $currentPrice): FavoritePriceChangeResult
    {
        $previous = $favorite->total_price_snapshot ?? $favorite->price_at_save;
        $current = $currentPrice['total'] ?? $currentPrice['per_night'];

        if ($previous === null || $current === null) {
            return new FavoritePriceChangeResult('unknown', false, 0.0, 0.0, $previous === null ? null : (float) $previous, $current);
        }

        $previousValue = round((float) $previous, 2);
        $currentValue = round((float) $current, 2);
        $amount = round($currentValue - $previousValue, 2);
        $changed = abs($amount) > 0.01;
        $percent = $previousValue > 0 ? round(($amount / $previousValue) * 100, 2) : 0.0;

        return new FavoritePriceChangeResult(
            state: $amount < -0.01 ? 'dropped' : ($amount > 0.01 ? 'increased' : 'same'),
            changed: $changed,
            amount: $amount,
            percent: $percent,
            previousTotal: $previousValue,
            currentTotal: $currentValue,
        );
    }

    /**
     * @return array{per_night:float,total:?float,deposit:float,discount:float,currency:string}
     */
    private function currentPrice(SleepingPlace $place, FavoriteContext $context, ?User $guest): array
    {
        $checkIn = $context->checkInDate();
        $checkOut = $context->checkOutDate();
        $currency = strtoupper($place->currency ?: 'EUR');
        $perNight = round((float) $place->base_price_per_night, 2);

        if (! $checkIn || ! $checkOut || $checkOut->lessThanOrEqualTo($checkIn)) {
            return [
                'per_night' => $perNight,
                'total' => null,
                'deposit' => round((float) ($place->deposit_amount ?? 0), 2),
                'discount' => 0.0,
                'currency' => $currency,
            ];
        }

        $quote = $this->pricing->calculate($guest ?: new User, $place, $checkIn, $checkOut, max(1, $context->guestsCount));

        return [
            'per_night' => $perNight,
            'total' => $quote->totalAmount,
            'deposit' => $quote->depositAmount,
            'discount' => round($quote->weeklyDiscountAmount + $quote->monthlyDiscountAmount, 2),
            'currency' => $quote->currency,
        ];
    }

    private function currentAvailability(SleepingPlace $place, ?CarbonImmutable $checkIn, ?CarbonImmutable $checkOut): ?bool
    {
        if (! $checkIn || ! $checkOut || $checkOut->lessThanOrEqualTo($checkIn)) {
            return null;
        }

        return $this->availability->isAvailable($place, $checkIn, $checkOut);
    }

    private function nights(?CarbonImmutable $checkIn, ?CarbonImmutable $checkOut): ?int
    {
        if (! $checkIn || ! $checkOut || $checkOut->lessThanOrEqualTo($checkIn)) {
            return null;
        }

        return (int) $checkIn->diffInDays($checkOut);
    }
}
