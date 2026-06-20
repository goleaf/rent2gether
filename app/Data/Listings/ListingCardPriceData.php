<?php

namespace App\Data\Listings;

final readonly class ListingCardPriceData
{
    public function __construct(
        public float $pricePerNight,
        public ?float $totalPrice,
        public string $currency,
        public ?int $nightsCount,
        public ?int $calendarDaysCount,
        public bool $hasDiscount,
        public float $discountAmount,
        public bool $hasDeposit,
        public float $depositAmount,
        public bool $hasFreeCancellation,
        public bool $hasCleaningFee = false,
        public float $cleaningFeeAmount = 0.0,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'price_per_night' => $this->pricePerNight,
            'total_price' => $this->totalPrice,
            'currency' => $this->currency,
            'nights_count' => $this->nightsCount,
            'calendar_days_count' => $this->calendarDaysCount,
            'has_discount' => $this->hasDiscount,
            'discount_amount' => $this->discountAmount,
            'has_deposit' => $this->hasDeposit,
            'deposit_amount' => $this->depositAmount,
            'has_free_cancellation' => $this->hasFreeCancellation,
            'has_cleaning_fee' => $this->hasCleaningFee,
            'cleaning_fee_amount' => $this->cleaningFeeAmount,
        ];
    }
}
