<?php

namespace App\Data;

use App\Enums\CancellationPolicy;

final readonly class RefundEstimate
{
    /**
     * @param  list<array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool}>  $lines
     */
    public function __construct(
        public CancellationPolicy $policy,
        public string $currency,
        public float $paidAmount,
        public float $refundAmount,
        public float $depositRefundAmount,
        public float $nonRefundableAmount,
        public float $penaltyAmount,
        public bool $depositRefunded,
        public string $explanationKey,
        public string $window,
        public array $lines,
    ) {}

    /**
     * @return array{
     *     policy:string,
     *     currency:string,
     *     paid_amount:float,
     *     refund_amount:float,
     *     deposit_refund_amount:float,
     *     non_refundable_amount:float,
     *     penalty_amount:float,
     *     deposit_refunded:bool,
     *     explanation_key:string,
     *     window:string,
     *     lines:list<array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool}>
     * }
     */
    public function toArray(): array
    {
        return [
            'policy' => $this->policy->value,
            'currency' => $this->currency,
            'paid_amount' => $this->paidAmount,
            'refund_amount' => $this->refundAmount,
            'deposit_refund_amount' => $this->depositRefundAmount,
            'non_refundable_amount' => $this->nonRefundableAmount,
            'penalty_amount' => $this->penaltyAmount,
            'deposit_refunded' => $this->depositRefunded,
            'explanation_key' => $this->explanationKey,
            'window' => $this->window,
            'lines' => $this->lines,
        ];
    }

    /**
     * @return array{
     *     refund_amount:float,
     *     penalty_amount:float,
     *     deposit_refunded:bool,
     *     deposit_refund_amount:float,
     *     non_refundable_amount:float,
     *     explanation:string,
     *     reason:string,
     *     lines:list<array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool}>
     * }
     */
    public function toLegacyArray(): array
    {
        $explanation = __($this->explanationKey);

        return [
            'refund_amount' => $this->refundAmount,
            'penalty_amount' => $this->penaltyAmount,
            'deposit_refunded' => $this->depositRefunded,
            'deposit_refund_amount' => $this->depositRefundAmount,
            'non_refundable_amount' => $this->nonRefundableAmount,
            'explanation' => $explanation,
            'reason' => $explanation,
            'lines' => $this->lines,
        ];
    }
}
