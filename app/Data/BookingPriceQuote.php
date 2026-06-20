<?php

namespace App\Data;

final readonly class BookingPriceQuote
{
    /**
     * @param  list<array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool,metadata:array<string, mixed>}>  $lineItems
     * @param  list<array{date:string,weekday:string,price:float,source:string}>  $datePrices
     * @param  list<string>  $warnings
     */
    public function __construct(
        public int $nightsCount,
        public int $calendarDaysCount,
        public int $weekdayCount,
        public int $weekendCount,
        public string $checkInWeekday,
        public string $checkOutWeekday,
        public string $currency,
        public float $baseAmount,
        public float $dateOverrideAmount,
        public float $weekendAdjustmentAmount,
        public float $weeklyDiscountAmount,
        public float $monthlyDiscountAmount,
        public float $cleaningFeeAmount,
        public float $depositAmount,
        public float $serviceFeeAmount,
        public float $subtotalAmount,
        public float $totalAmount,
        public float $refundableAmount,
        public float $nonRefundableAmount,
        public string $cancellationDeadline,
        public string $paymentDeadline,
        public array $lineItems,
        public array $datePrices,
        public array $warnings = [],
    ) {}

    /**
     * @return array{
     *     nights_count:int,
     *     calendar_days_count:int,
     *     weekday_count:int,
     *     weekend_count:int,
     *     check_in_weekday:string,
     *     check_out_weekday:string,
     *     currency:string,
     *     base_amount:float,
     *     date_override_amount:float,
     *     weekend_adjustment_amount:float,
     *     weekly_discount_amount:float,
     *     monthly_discount_amount:float,
     *     cleaning_fee_amount:float,
     *     deposit_amount:float,
     *     service_fee_amount:float,
     *     subtotal_amount:float,
     *     total_amount:float,
     *     refundable_amount:float,
     *     non_refundable_amount:float,
     *     cancellation_deadline:string,
     *     payment_deadline:string,
     *     line_items:list<array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool,metadata:array<string, mixed>}>,
     *     date_prices:list<array{date:string,weekday:string,price:float,source:string}>,
     *     warnings:list<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'nights_count' => $this->nightsCount,
            'calendar_days_count' => $this->calendarDaysCount,
            'weekday_count' => $this->weekdayCount,
            'weekend_count' => $this->weekendCount,
            'check_in_weekday' => $this->checkInWeekday,
            'check_out_weekday' => $this->checkOutWeekday,
            'currency' => $this->currency,
            'base_amount' => $this->baseAmount,
            'date_override_amount' => $this->dateOverrideAmount,
            'weekend_adjustment_amount' => $this->weekendAdjustmentAmount,
            'weekly_discount_amount' => $this->weeklyDiscountAmount,
            'monthly_discount_amount' => $this->monthlyDiscountAmount,
            'cleaning_fee_amount' => $this->cleaningFeeAmount,
            'deposit_amount' => $this->depositAmount,
            'service_fee_amount' => $this->serviceFeeAmount,
            'subtotal_amount' => $this->subtotalAmount,
            'total_amount' => $this->totalAmount,
            'refundable_amount' => $this->refundableAmount,
            'non_refundable_amount' => $this->nonRefundableAmount,
            'cancellation_deadline' => $this->cancellationDeadline,
            'payment_deadline' => $this->paymentDeadline,
            'line_items' => $this->lineItems,
            'date_prices' => $this->datePrices,
            'warnings' => $this->warnings,
        ];
    }
}
