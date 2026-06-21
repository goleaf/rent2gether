<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use App\Services\Pricing\PricingService;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingExtensionPriceService
{
    public function __construct(
        private readonly PricingService $pricing,
    ) {}

    public function calculateAdditionalNights(BookingExtension $extension): int
    {
        return (int) $this->currentCheckout($extension)->diffInDays($this->newCheckout($extension));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function createPriceLines(BookingExtension $extension): Collection
    {
        return app(BookingExtensionLineService::class)->rebuildLines($extension);
    }

    public function calculateAccommodationAmount(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['accommodation_amount'], $extension->currency ?: 'EUR');
    }

    public function calculateExtensionDiscount(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['discount_amount'], $extension->currency ?: 'EUR');
    }

    public function calculateServiceFee(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['service_fee_amount'], $extension->currency ?: 'EUR');
    }

    public function calculateCleaningFee(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['cleaning_fee_amount'], $extension->currency ?: 'EUR');
    }

    public function calculateAdditionalDeposit(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['additional_deposit_amount'], $extension->currency ?: 'EUR');
    }

    public function calculateHostPayout(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['host_payout_amount'], $extension->currency ?: 'EUR');
    }

    public function calculateRefundableAmount(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['refundable_amount'], $extension->currency ?: 'EUR');
    }

    public function calculateNonRefundableAmount(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['non_refundable_amount'], $extension->currency ?: 'EUR');
    }

    public function calculateTotalPayable(BookingExtension $extension): Money
    {
        return new Money($this->priceData($extension)['total_payable'], $extension->currency ?: 'EUR');
    }

    /**
     * @return array<string, mixed>
     */
    public function priceData(BookingExtension $extension): array
    {
        $extension->loadMissing('booking.guest', 'sleepingPlace');
        $booking = $extension->booking;
        $place = $extension->sleepingPlace ?? $booking?->sleepingPlace;
        $guest = $booking?->guest;

        if (! $booking || ! $place || ! $guest) {
            return $this->emptyPriceData($extension->currency ?: 'EUR');
        }

        $quote = $this->pricing
            ->calculate($guest, $place, $this->currentCheckout($extension), $this->newCheckout($extension), (int) $booking->guests_count)
            ->toArray();

        $accommodation = $this->money($quote['base_amount'] + $quote['weekend_adjustment_amount'] + $quote['date_override_amount']);
        $discount = $this->money($quote['weekly_discount_amount'] + $quote['monthly_discount_amount']);
        $serviceFee = $this->money($quote['service_fee_amount']);
        $cleaningFee = 0.0;
        $additionalDeposit = 0.0;
        $subtotal = $this->money(max(0, $accommodation - $discount));
        $total = $this->money($subtotal + $serviceFee + $cleaningFee + $additionalDeposit);

        return [
            'additional_nights_count' => (int) $quote['nights_count'],
            'additional_chargeable_days_count' => (int) $quote['nights_count'],
            'additional_calendar_presence_days_count' => (int) $quote['nights_count'] + 1,
            'accommodation_amount' => $accommodation,
            'discount_amount' => $discount,
            'service_fee_amount' => $serviceFee,
            'cleaning_fee_amount' => $cleaningFee,
            'additional_deposit_amount' => $additionalDeposit,
            'total_payable' => $total,
            'host_payout_amount' => $this->money($subtotal + $cleaningFee),
            'refundable_amount' => $additionalDeposit,
            'non_refundable_amount' => $this->money($subtotal + $serviceFee + $cleaningFee),
            'currency' => $quote['currency'] ?: 'EUR',
            'date_prices' => $quote['date_prices'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPriceData(string $currency): array
    {
        return [
            'additional_nights_count' => 0,
            'additional_chargeable_days_count' => 0,
            'additional_calendar_presence_days_count' => 0,
            'accommodation_amount' => 0.0,
            'discount_amount' => 0.0,
            'service_fee_amount' => 0.0,
            'cleaning_fee_amount' => 0.0,
            'additional_deposit_amount' => 0.0,
            'total_payable' => 0.0,
            'host_payout_amount' => 0.0,
            'refundable_amount' => 0.0,
            'non_refundable_amount' => 0.0,
            'currency' => $currency,
            'date_prices' => [],
        ];
    }

    private function currentCheckout(BookingExtension $extension): CarbonImmutable
    {
        return CarbonImmutable::parse($extension->current_check_out_date ?? $extension->current_checkout_date)->startOfDay();
    }

    private function newCheckout(BookingExtension $extension): CarbonImmutable
    {
        return CarbonImmutable::parse($extension->new_check_out_date ?? $extension->requested_new_checkout_date)->startOfDay();
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
