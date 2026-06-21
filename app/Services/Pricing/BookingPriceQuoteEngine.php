<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;
use App\Models\BookingQuoteLine;
use App\Models\PromoCode;
use App\Models\SleepingPlace;
use App\Models\SleepingPlacePricingSetting;
use App\Models\User;
use App\Services\Bookings\BookingQuoteNumberService;
use App\Services\Bookings\StayLengthCalculatorService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingPriceQuoteEngine
{
    private const PRICING_WARNING_KEYS = [
        'early_check_in_host_approval_required',
        'late_checkout_host_approval_required',
        'promo_code_invalid',
    ];

    public function __construct(
        private readonly BookingQuoteNumberService $numbers,
        private readonly StayLengthCalculatorService $stayLength,
        private readonly PricingSettingsService $settings,
        private readonly NightlyPriceLineService $nightlyLines,
        private readonly DiscountCalculatorService $discounts,
        private readonly PromoCodeService $promoCodes,
        private readonly FeeCalculatorService $fees,
        private readonly ServiceFeeCalculatorService $serviceFees,
        private readonly TaxCalculatorService $taxes,
        private readonly DepositCalculatorService $deposits,
        private readonly HostPayoutCalculatorService $hostPayout,
        private readonly RefundabilityCalculatorService $refundability,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function recalculate(User $guest, SleepingPlace $place, array $data): BookingQuote
    {
        $checkIn = CarbonImmutable::parse($data['check_in_date'])->startOfDay();
        $checkOut = CarbonImmutable::parse($data['check_out_date'])->startOfDay();
        $nights = $this->stayLength->calculateNights($checkIn, $checkOut);

        $quote = BookingQuote::query()->create([
            'quote_number' => $this->numbers->generate(),
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'room_id' => $place->room_id,
            'property_id' => $place->property_id,
            'host_user_id' => $place->user_id ?: $place->property?->host_user_id,
            'rental_mode' => (string) ($data['rental_mode'] ?? 'nightly'),
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'check_in_time' => $data['check_in_time'] ?? null,
            'check_out_time' => $data['check_out_time'] ?? null,
            'nights_count' => $nights,
            'chargeable_days_count' => $nights,
            'calendar_presence_days_count' => $this->stayLength->calculateCalendarPresenceDays($checkIn, $checkOut),
            'guests_count' => max(1, (int) ($data['guests_count'] ?? 1)),
            'early_check_in_requested' => (bool) ($data['early_check_in_requested'] ?? false),
            'late_check_out_requested' => (bool) ($data['late_check_out_requested'] ?? false),
            'promo_code' => $data['promo_code'] ?? null,
            'currency' => strtoupper((string) ($place->currency ?: 'EUR')),
            'status' => BookingQuote::STATUS_DRAFT,
        ]);

        return $this->recalculateExistingQuote($quote);
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function recalculateExistingQuote(BookingQuote $quote, array $changes = []): BookingQuote
    {
        if ($changes !== []) {
            $quote->forceFill($changes)->save();
        }

        $quote->loadMissing(['guest', 'sleepingPlace.pricingSettings', 'sleepingPlace.datePrices', 'sleepingPlace.pricingDiscountRules']);
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);
        $quote->forceFill([
            'currency' => strtoupper((string) ($quote->currency ?: $settings->currency)),
            'included_guests_count' => (int) $settings->included_guests_count,
            'extra_guests_count' => max(0, (int) $quote->guests_count - (int) $settings->included_guests_count),
        ])->save();

        $quote->lines()->delete();
        $quote->validationResults()->whereIn('validation_key', self::PRICING_WARNING_KEYS)->delete();

        $validation = $this->validateQuote($quote);

        if ($validation->contains('blocking', true)) {
            return $this->markQuoteInvalid($quote, (string) $validation->first()['validation_key']);
        }

        $this->buildQuoteLines($quote);

        return $quote->fresh(['lines', 'validationResults']);
    }

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    public function buildQuoteLines(BookingQuote $quote): Collection
    {
        $lines = $this->nightlyLines->buildNightLines($quote);
        $accommodationBeforeDiscount = $this->nightlyLines->calculateAccommodationBeforeDiscount($quote);
        $quote->forceFill(['accommodation_amount' => $accommodationBeforeDiscount])->save();

        $discountLines = $this->createDiscountLines($quote);
        $discountAmount = $this->money($discountLines->sum(fn (BookingQuoteLine $line): float => abs((float) $line->amount)));
        $accommodationAfterDiscount = $this->money(max(0, $accommodationBeforeDiscount - $discountAmount));
        $quote->forceFill(['discount_amount' => $discountAmount])->save();

        $feeLines = $this->createFeeLines($quote, $accommodationAfterDiscount);
        $depositLine = $this->createDepositLine($quote);
        $taxLines = $this->createTaxLines($quote);

        $cleaningFee = $this->lineAmount($quote, 'cleaning_fee');
        $guestServiceFee = $this->lineAmount($quote, 'service_fee');
        $taxAmount = $this->lineAmount($quote, 'tax_future');
        $cityFee = $this->lineAmount($quote, 'city_fee_future');
        $deposit = $this->lineAmount($quote, 'deposit');
        $depositPayable = $depositLine instanceof BookingQuoteLine && $depositLine->is_payable_now ? $deposit : 0.0;
        $totalWithoutDeposit = $this->money($accommodationAfterDiscount
            + $this->lineAmount($quote, 'early_check_in_fee')
            + $this->lineAmount($quote, 'late_checkout_fee')
            + $this->lineAmount($quote, 'extra_guest_fee')
            + $cleaningFee
            + $guestServiceFee
            + $taxAmount
            + $cityFee);
        $totalPayable = $this->money($totalWithoutDeposit + $depositPayable);

        $quote->forceFill([
            'cleaning_fee_amount' => $cleaningFee,
            'service_fee_amount' => $guestServiceFee,
            'tax_amount' => $taxAmount,
            'city_fee_amount' => $cityFee,
            'deposit_amount' => $deposit,
            'total_without_deposit' => $totalWithoutDeposit,
            'total_payable' => $totalPayable,
            'pricing_status' => 'calculated',
            'status' => BookingQuote::STATUS_VALID,
        ])->save();

        $quote->forceFill([
            'host_payout_preview_amount' => $this->hostPayout->calculateHostPayout($quote),
            'refundable_amount' => $this->refundability->calculateRefundableAmount($quote),
            'non_refundable_amount' => $this->refundability->calculateNonRefundableAmount($quote),
            'requires_host_time_approval' => $this->requiresHostTimeApproval($quote),
        ])->save();

        $this->createTimeApprovalWarnings($quote);

        return $lines
            ->merge($discountLines)
            ->merge($feeLines)
            ->merge($taxLines)
            ->when($depositLine instanceof BookingQuoteLine, fn (Collection $collection): Collection => $collection->push($depositLine))
            ->values();
    }

    /**
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateQuote(BookingQuote $quote): Collection
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);
        $results = collect();

        if ((float) $settings->base_nightly_price <= 0) {
            $results->push($this->validationResult('price_missing', true));
        }

        if ((int) $quote->guests_count > (int) $settings->max_guests_count) {
            $results->push($this->validationResult('guests_count_too_high', true, ['count' => (int) $settings->max_guests_count]));
        }

        return $results;
    }

    public function markQuoteInvalid(BookingQuote $quote, string $reason): BookingQuote
    {
        $quote->validationResults()->create($this->validationResult($reason, true));
        $quote->forceFill([
            'status' => BookingQuote::STATUS_INVALID,
            'validation_status' => 'invalid',
            'pricing_status' => 'failed',
        ])->save();

        return $quote->fresh(['lines', 'validationResults']);
    }

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    private function createDiscountLines(BookingQuote $quote): Collection
    {
        $lines = collect();
        $sort = 100;
        $discounts = $this->discounts->getApplicableDiscounts($quote);
        $discounts = $this->appendPromoDiscount($quote, $discounts);
        $remaining = $this->money($quote->accommodation_amount);

        foreach ($discounts as $discount) {
            $amount = min($remaining, $this->money($discount['amount']));

            if ($amount <= 0) {
                continue;
            }

            $lines->push($quote->lines()->create([
                'line_type' => $discount['line_type'],
                'label_key' => $discount['label_key'],
                'quantity' => 1,
                'unit_amount' => -$amount,
                'amount' => -$amount,
                'currency' => $quote->currency,
                'is_discount' => true,
                'is_fee' => false,
                'is_deposit' => false,
                'is_refundable' => false,
                'is_payable_now' => true,
                'sort_order' => $sort++,
            ]));

            $remaining = $this->money($remaining - $amount);
        }

        return $lines;
    }

    /**
     * @param  Collection<int, array{type:string,line_type:string,label_key:string,amount:float,priority:int,allow_stacking:bool,rule_id:int|null}>  $discounts
     * @return Collection<int, array{type:string,line_type:string,label_key:string,amount:float,priority:int,allow_stacking:bool,rule_id:int|null}>
     */
    private function appendPromoDiscount(BookingQuote $quote, Collection $discounts): Collection
    {
        if (! $quote->promo_code) {
            $quote->forceFill(['promo_code_status' => null])->save();

            return $discounts;
        }

        $validation = $this->promoCodes->validatePromoCode($quote->guest, $quote, (string) $quote->promo_code);
        $quote->forceFill(['promo_code_status' => $validation['status']])->save();

        if (! $validation['valid'] || ! $validation['promo_code'] instanceof PromoCode) {
            $quote->validationResults()->create($this->validationResult('promo_code_invalid', false, [
                'reason' => $validation['status'],
            ]));

            return $discounts;
        }

        $discounts->push([
            'type' => 'promo',
            'line_type' => 'promo_discount',
            'label_key' => 'pricing.line_types.promo_discount',
            'amount' => $this->promoCodes->calculatePromoDiscount($quote, $validation['promo_code']),
            'priority' => 100,
            'allow_stacking' => true,
            'rule_id' => null,
        ]);

        return $discounts;
    }

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    private function createFeeLines(BookingQuote $quote, float $accommodationAfterDiscount): Collection
    {
        $quote->forceFill(['discount_amount' => $this->money((float) $quote->accommodation_amount - $accommodationAfterDiscount)])->save();
        $lines = collect();
        $fees = [
            'early_check_in_fee' => $this->fees->calculateEarlyCheckInFee($quote),
            'late_checkout_fee' => $this->fees->calculateLateCheckoutFee($quote),
            'extra_guest_fee' => $this->fees->calculateExtraGuestFee($quote),
            'cleaning_fee' => $this->fees->calculateCleaningFee($quote),
        ];
        $sort = 200;

        foreach ($fees as $type => $amount) {
            if ($amount <= 0) {
                continue;
            }

            $lines->push($quote->lines()->create($this->linePayload(
                type: $type,
                amount: $amount,
                currency: $quote->currency,
                sortOrder: $sort++,
                isFee: true,
                isRefundable: false,
            )));
        }

        $quote->forceFill([
            'cleaning_fee_amount' => $this->money($fees['cleaning_fee']),
        ])->save();

        $guestServiceFee = $this->serviceFees->calculateGuestServiceFee($quote);

        if ($guestServiceFee > 0) {
            $lines->push($quote->lines()->create($this->linePayload(
                type: 'service_fee',
                amount: $guestServiceFee,
                currency: $quote->currency,
                sortOrder: 240,
                isFee: true,
                isRefundable: false,
            )));
        }

        return $lines;
    }

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    private function createTaxLines(BookingQuote $quote): Collection
    {
        $lines = collect();
        $taxes = [
            'tax_future' => $this->taxes->calculateTaxAmount($quote),
            'city_fee_future' => $this->taxes->calculateCityFee($quote),
        ];
        $sort = 250;

        foreach ($taxes as $type => $amount) {
            if ($amount <= 0) {
                continue;
            }

            $lines->push($quote->lines()->create($this->linePayload(
                type: $type,
                amount: $amount,
                currency: $quote->currency,
                sortOrder: $sort++,
                isFee: true,
                isRefundable: false,
            )));
        }

        return $lines;
    }

    private function createDepositLine(BookingQuote $quote): ?BookingQuoteLine
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);
        $deposit = $this->deposits->calculateDeposit($quote);

        if ($deposit <= 0) {
            return null;
        }

        return $quote->lines()->create($this->linePayload(
            type: 'deposit',
            amount: $deposit,
            currency: $quote->currency,
            sortOrder: 300,
            isDeposit: true,
            isRefundable: (bool) $settings->deposit_refundable,
            isPayableNow: (bool) $settings->deposit_payable_now,
        ));
    }

    private function requiresHostTimeApproval(BookingQuote $quote): bool
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);

        return ($quote->early_check_in_requested && $settings->early_check_in_mode === SleepingPlacePricingSetting::TIME_MODE_HOST_APPROVAL)
            || ($quote->late_check_out_requested && $settings->late_checkout_mode === SleepingPlacePricingSetting::TIME_MODE_HOST_APPROVAL);
    }

    private function createTimeApprovalWarnings(BookingQuote $quote): void
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);

        if ($quote->early_check_in_requested && $settings->early_check_in_mode === SleepingPlacePricingSetting::TIME_MODE_HOST_APPROVAL) {
            $quote->validationResults()->create($this->validationResult('early_check_in_host_approval_required', false));
        }

        if ($quote->late_check_out_requested && $settings->late_checkout_mode === SleepingPlacePricingSetting::TIME_MODE_HOST_APPROVAL) {
            $quote->validationResults()->create($this->validationResult('late_checkout_host_approval_required', false));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function linePayload(
        string $type,
        float $amount,
        string $currency,
        int $sortOrder,
        bool $isFee = false,
        bool $isDeposit = false,
        bool $isRefundable = false,
        bool $isPayableNow = true,
    ): array {
        return [
            'line_type' => $type,
            'label_key' => 'pricing.line_types.'.$type,
            'quantity' => 1,
            'unit_amount' => $this->money($amount),
            'amount' => $this->money($amount),
            'currency' => $currency,
            'is_discount' => false,
            'is_fee' => $isFee,
            'is_deposit' => $isDeposit,
            'is_refundable' => $isRefundable,
            'is_payable_now' => $isPayableNow,
            'sort_order' => $sortOrder,
        ];
    }

    /**
     * @return array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}
     */
    private function validationResult(string $key, bool $blocking, array $params = []): array
    {
        return [
            'validation_key' => $key,
            'severity' => $blocking ? 'blocking' : 'warning',
            'message_key' => 'pricing.validation.'.$key,
            'message_params_json' => $params,
            'blocking' => $blocking,
            'visible_to_guest' => true,
            'visible_to_host' => false,
        ];
    }

    private function lineAmount(BookingQuote $quote, string $type): float
    {
        return $this->money($quote->lines()->where('line_type', $type)->sum('amount'));
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
