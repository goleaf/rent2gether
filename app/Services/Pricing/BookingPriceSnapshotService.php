<?php

namespace App\Services\Pricing;

use App\Models\Booking;
use App\Models\BookingPriceSnapshot;
use App\Models\BookingQuote;
use App\Models\PromoCode;

class BookingPriceSnapshotService
{
    public function __construct(
        private readonly PricingSettingsService $settings,
        private readonly ServiceFeeCalculatorService $serviceFees,
    ) {}

    public function createFromQuote(Booking $booking, BookingQuote $quote): BookingPriceSnapshot
    {
        $quote->loadMissing(['lines', 'sleepingPlace']);
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);
        $hostServiceFee = $this->serviceFees->calculateHostServiceFee($quote);
        $accommodationAfterDiscount = $this->money((float) $quote->accommodation_amount - (float) $quote->discount_amount);

        return BookingPriceSnapshot::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'booking_quote_id' => $quote->id,
                ...$this->rebuildSnapshotJson($quote),
                'accommodation_before_discount' => $quote->accommodation_amount,
                'discount_amount' => $quote->discount_amount,
                'accommodation_after_discount' => $accommodationAfterDiscount,
                'early_check_in_fee' => $this->lineAmount($quote, 'early_check_in_fee'),
                'late_checkout_fee' => $this->lineAmount($quote, 'late_checkout_fee'),
                'extra_guest_fee' => $this->lineAmount($quote, 'extra_guest_fee'),
                'cleaning_fee' => $quote->cleaning_fee_amount,
                'guest_service_fee' => $quote->service_fee_amount,
                'host_service_fee' => $hostServiceFee,
                'tax_amount' => $quote->tax_amount,
                'city_fee' => $quote->city_fee_amount,
                'deposit_amount' => $quote->deposit_amount,
                'total_without_deposit' => $quote->total_without_deposit,
                'total_payable' => $quote->total_payable,
                'host_payout_amount' => $quote->host_payout_preview_amount,
                'refundable_amount' => $quote->refundable_amount,
                'non_refundable_amount' => $quote->non_refundable_amount,
                'currency' => $quote->currency ?: $settings->currency,
            ],
        );
    }

    public function getForBooking(Booking $booking): BookingPriceSnapshot
    {
        return $booking->priceSnapshot()->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    public function rebuildSnapshotJson(BookingQuote $quote): array
    {
        $quote->loadMissing(['lines', 'sleepingPlace']);
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);
        $promo = $quote->promo_code
            ? PromoCode::query()->where('code', (string) $quote->promo_code)->first()
            : null;

        return [
            'pricing_settings_snapshot_json' => $settings->toArray(),
            'quote_lines_snapshot_json' => $quote->lines
                ->sortBy('sort_order')
                ->map(fn ($line): array => [
                    'line_type' => $line->line_type,
                    'label_key' => $line->label_key,
                    'date' => $line->date?->toDateString(),
                    'quantity' => (float) $line->quantity,
                    'unit_amount' => (float) $line->unit_amount,
                    'amount' => (float) $line->amount,
                    'currency' => $line->currency,
                    'is_discount' => (bool) $line->is_discount,
                    'is_fee' => (bool) $line->is_fee,
                    'is_deposit' => (bool) $line->is_deposit,
                    'is_refundable' => (bool) $line->is_refundable,
                    'is_payable_now' => (bool) $line->is_payable_now,
                ])
                ->values()
                ->all(),
            'discounts_snapshot_json' => $quote->lines
                ->where('is_discount', true)
                ->map(fn ($line): array => [
                    'line_type' => $line->line_type,
                    'amount' => abs((float) $line->amount),
                    'currency' => $line->currency,
                ])
                ->values()
                ->all(),
            'promo_code_snapshot_json' => $promo?->toArray(),
        ];
    }

    private function lineAmount(BookingQuote $quote, string $type): float
    {
        return $this->money($quote->lines->where('line_type', $type)->sum('amount'));
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
