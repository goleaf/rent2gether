<?php

namespace App\Services\Listings;

use App\Data\Listings\ListingCardContext;
use App\Data\Listings\ListingCardPriceData;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Pricing\PricingService;

class ListingCardPriceService
{
    public function __construct(private readonly PricingService $pricing) {}

    public function getPriceForCard(SleepingPlace $place, ListingCardContext $context): ListingCardPriceData
    {
        $currency = strtoupper($place->currency ?: $context->currency ?: 'EUR');
        $freeCancellation = $this->hasFreeCancellation($place);

        if (! $context->hasDates()) {
            return $this->getPriceWithoutDates($place, $currency, $freeCancellation);
        }

        return $this->getTotalForDates($place, $context, $currency, $freeCancellation);
    }

    public function getPriceWithoutDates(SleepingPlace $place, ?string $currency = null, ?bool $freeCancellation = null): ListingCardPriceData
    {
        $currency ??= strtoupper($place->currency ?: 'EUR');
        $deposit = (float) ($place->deposit_amount ?: 0);

        return new ListingCardPriceData(
            pricePerNight: (float) $place->base_price_per_night,
            totalPrice: null,
            currency: $currency,
            nightsCount: null,
            calendarDaysCount: null,
            hasDiscount: false,
            discountAmount: 0.0,
            hasDeposit: $deposit > 0,
            depositAmount: $deposit,
            hasFreeCancellation: $freeCancellation ?? $this->hasFreeCancellation($place),
            hasCleaningFee: (float) ($place->cleaning_fee ?: 0) > 0,
            cleaningFeeAmount: (float) ($place->cleaning_fee ?: 0),
        );
    }

    public function getTotalForDates(
        SleepingPlace $place,
        ListingCardContext $context,
        ?string $currency = null,
        ?bool $freeCancellation = null,
    ): ListingCardPriceData {
        $currency ??= strtoupper($place->currency ?: $context->currency ?: 'EUR');

        try {
            $guest = $context->userId ? User::query()->find($context->userId) : null;
            $quote = $this->pricing
                ->calculate($guest ?: new User(['name' => 'Guest']), $place, $context->checkInDate, $context->checkOutDate, max(1, $context->guestsCount))
                ->toArray();
            $discount = (float) ($quote['weekly_discount_amount'] ?? 0) + (float) ($quote['monthly_discount_amount'] ?? 0);
            $deposit = (float) ($quote['deposit_amount'] ?? $place->deposit_amount ?? 0);

            return new ListingCardPriceData(
                pricePerNight: (float) $place->base_price_per_night,
                totalPrice: (float) ($quote['total_amount'] ?? 0),
                currency: strtoupper($quote['currency'] ?? $currency),
                nightsCount: (int) ($quote['nights_count'] ?? $context->nights() ?? 0),
                calendarDaysCount: (int) ($quote['calendar_days_count'] ?? $context->calendarDays() ?? 0),
                hasDiscount: $discount > 0,
                discountAmount: $discount,
                hasDeposit: $deposit > 0,
                depositAmount: $deposit,
                hasFreeCancellation: $freeCancellation ?? $this->hasFreeCancellation($place),
                hasCleaningFee: (float) ($quote['cleaning_fee_amount'] ?? 0) > 0,
                cleaningFeeAmount: (float) ($quote['cleaning_fee_amount'] ?? 0),
            );
        } catch (\Throwable) {
            return $this->getPriceWithoutDates($place, $currency, $freeCancellation);
        }
    }

    private function hasFreeCancellation(SleepingPlace $place): bool
    {
        return ($place->property?->host?->hostProfile?->default_cancellation_policy ?: 'flexible') === 'flexible';
    }
}
