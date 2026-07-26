<?php

namespace App\Services\Waitlist;

use App\Data\Waitlist\DateRange;
use App\Data\Waitlist\PriceData;
use App\Data\Waitlist\WaitlistEligibilityResult;
use App\Models\SleepingPlace;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;
use App\Services\Availability\AvailabilityService;
use App\Services\Pricing\PricingService;
use Illuminate\Support\Collection;

class WaitlistQueueService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
    ) {}

    public function getQueueForPlace(SleepingPlace $place, DateRange $range): Collection
    {
        return WaitlistItem::query()
            ->select([
                'id',
                'user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'desired_check_in_date',
                'desired_check_out_date',
                'nights_count',
                'guests_count',
                'max_price_per_night',
                'max_total_price',
                'max_deposit',
                'currency',
                'priority_score',
                'position',
                'skipped_count',
                'max_skips',
                'expires_at',
                'added_at',
                'ready_to_book_immediately',
                'auto_send_request',
                'auto_create_booking_draft',
                'min_nights',
                'max_nights',
            ])
            ->forSleepingPlace($place)
            ->active()
            ->forDateRange($range)
            ->orderedQueue()
            ->get();
    }

    public function calculatePosition(WaitlistItem $item): int
    {
        $range = $this->rangeForItem($item);

        $ids = $this->getQueueForPlace($item->sleepingPlace, $range)
            ->pluck('id')
            ->values();

        $index = $ids->search($item->id);

        return $index === false ? 0 : ((int) $index + 1);
    }

    public function recalculatePositions(SleepingPlace $place): void
    {
        $groups = WaitlistItem::query()
            ->select(['id', 'sleeping_place_id', 'desired_check_in_date', 'desired_check_out_date'])
            ->forSleepingPlace($place)
            ->active()
            ->get()
            ->groupBy(fn (WaitlistItem $item): string => $item->desired_check_in_date?->toDateString().'|'.$item->desired_check_out_date?->toDateString());

        foreach ($groups as $group) {
            $first = $group->first();

            if (! $first instanceof WaitlistItem || ! $first->desired_check_in_date || ! $first->desired_check_out_date) {
                continue;
            }

            $this->getQueueForPlace($place, $this->rangeForItem($first))
                ->values()
                ->each(fn (WaitlistItem $queued, int $index) => WaitlistItem::query()
                    ->whereKey($queued->id)
                    ->update(['position' => $index + 1]));
        }
    }

    /**
     * @param  list<int>  $excludeItemIds
     */
    public function getNextEligibleGuest(SleepingPlace $place, DateRange $range, array $excludeItemIds = []): ?WaitlistItem
    {
        return $this->getQueueForPlace($place, $range)
            ->reject(fn (WaitlistItem $item): bool => in_array($item->id, $excludeItemIds, true))
            ->first(function (WaitlistItem $item) use ($place): bool {
                $result = $this->isEligible($item, $place, $this->priceDataFor($item));

                return $result->eligible;
            });
    }

    public function skipToNext(WaitlistOffer $offer): ?WaitlistOffer
    {
        $item = $offer->waitlistItem()->with('sleepingPlace')->first();

        if (! $item instanceof WaitlistItem) {
            return null;
        }

        $next = $this->getNextEligibleGuest(
            $item->sleepingPlace,
            $this->rangeForItem($item),
            [$item->id],
        );

        if (! $next instanceof WaitlistItem) {
            return null;
        }

        return app(WaitlistOfferService::class)->createOffer($next, $this->priceDataFor($next));
    }

    public function isEligible(WaitlistItem $item, SleepingPlace $place, PriceData $priceData): WaitlistEligibilityResult
    {
        $reasons = [];
        $range = $this->rangeForItem($item);

        if (! in_array($item->status, ['active', 'waiting'], true)) {
            $reasons[] = 'inactive';
        }

        if (! $range->valid() || $range->expired()) {
            $reasons[] = 'dates_expired';
        }

        if ($item->expires_at !== null && $item->expires_at->isPast()) {
            $reasons[] = 'expired';
        }

        if ((int) $item->skipped_count >= (int) $item->max_skips) {
            $reasons[] = 'too_many_skips';
        }

        if ((int) $item->guests_count > max(1, (int) $place->max_guests)) {
            $reasons[] = 'guest_count';
        }

        if ($item->min_nights && $range->nightsCount < (int) $item->min_nights) {
            $reasons[] = 'min_nights';
        }

        if ($item->max_nights && $range->nightsCount > (int) $item->max_nights) {
            $reasons[] = 'max_nights';
        }

        if (! $this->availability->isAvailable($place, $range->checkIn, $range->checkOut)) {
            $reasons[] = 'unavailable';
        }

        if ($item->max_price_per_night !== null && $priceData->pricePerNight > (float) $item->max_price_per_night) {
            $reasons[] = 'price_too_high';
        }

        if ($item->max_total_price !== null && $priceData->totalPrice > (float) $item->max_total_price) {
            $reasons[] = 'total_price_too_high';
        }

        if ($item->max_deposit !== null && $priceData->deposit > (float) $item->max_deposit) {
            $reasons[] = 'deposit_too_high';
        }

        return new WaitlistEligibilityResult($reasons === [], array_values(array_unique($reasons)), $priceData);
    }

    public function priceDataFor(WaitlistItem $item): PriceData
    {
        $item->loadMissing(['user:id', 'sleepingPlace:id,room_id,property_id,base_price_per_night,weekend_price,weekly_price,monthly_price,cleaning_fee,deposit_amount,currency,min_nights,max_nights,max_guests']);
        $range = $this->rangeForItem($item);
        $quote = $this->pricing->calculate(
            $item->user,
            $item->sleepingPlace,
            $range->checkIn,
            $range->checkOut,
            max(1, (int) $item->guests_count),
        );

        return new PriceData(
            pricePerNight: round((float) $item->sleepingPlace->base_price_per_night, 2),
            totalPrice: $quote->totalAmount,
            deposit: $quote->depositAmount,
            currency: $quote->currency,
        );
    }

    public function rangeForItem(WaitlistItem $item): DateRange
    {
        return new DateRange(
            $item->desired_check_in_date ?: $item->desired_check_in,
            $item->desired_check_out_date ?: $item->desired_check_out,
        );
    }
}
