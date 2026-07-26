<?php

namespace App\Models;

use Database\Factories\SleepingPlacePricingSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlacePricingSetting extends Model
{
    public const STRATEGY_PER_NIGHT = 'per_night';

    public const STRATEGY_PER_NIGHT_WITH_DISCOUNTS = 'per_night_with_discounts';

    public const STRATEGY_WEEKLY_PACKAGE = 'weekly_package';

    public const STRATEGY_MONTHLY_PACKAGE = 'monthly_package';

    public const STRATEGY_BEST_PRICE = 'best_price';

    public const FEE_FIXED = 'fixed';

    public const FEE_PERCENT = 'percent';

    public const FEE_NONE = 'none';

    public const TIME_MODE_NOT_ALLOWED = 'not_allowed';

    public const TIME_MODE_AUTO_FEE = 'auto_approved_with_fee';

    public const TIME_MODE_HOST_APPROVAL = 'requires_host_approval';

    public const TIME_MODE_FREE = 'free_if_available';

    /** @use HasFactory<SleepingPlacePricingSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'currency',
        'base_nightly_price',
        'weekday_price',
        'weekend_price',
        'holiday_price',
        'weekly_price',
        'monthly_price',
        'pricing_strategy',
        'weekend_days_json',
        'extra_guest_allowed',
        'included_guests_count',
        'max_guests_count',
        'extra_guest_fee',
        'early_check_in_mode',
        'early_check_in_fee',
        'late_checkout_mode',
        'late_checkout_fee',
        'cleaning_fee',
        'deposit_required',
        'deposit_amount',
        'deposit_payable_now',
        'deposit_refundable',
        'installment_payment_allowed',
        'pay_later_allowed',
        'pay_on_arrival_allowed',
        'all_fees_included',
        'show_total_price_upfront',
        'hidden_fees_disclosed',
        'guest_service_fee_type',
        'guest_service_fee_value',
        'host_service_fee_type',
        'host_service_fee_value',
        'tax_fee_type',
        'tax_fee_value',
        'city_fee_type',
        'city_fee_value',
        'active',
    ];

    /**
     * Defines how Laravel converts stored pricing settings into PHP values.
     */
    protected function casts(): array
    {
        return [
            'base_nightly_price' => 'decimal:2',
            'weekday_price' => 'decimal:2',
            'weekend_price' => 'decimal:2',
            'holiday_price' => 'decimal:2',
            'weekly_price' => 'decimal:2',
            'monthly_price' => 'decimal:2',
            'weekend_days_json' => 'array',
            'extra_guest_allowed' => 'boolean',
            'included_guests_count' => 'integer',
            'max_guests_count' => 'integer',
            'extra_guest_fee' => 'decimal:2',
            'early_check_in_fee' => 'decimal:2',
            'late_checkout_fee' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'deposit_required' => 'boolean',
            'deposit_amount' => 'decimal:2',
            'deposit_payable_now' => 'boolean',
            'deposit_refundable' => 'boolean',
            'installment_payment_allowed' => 'boolean',
            'pay_later_allowed' => 'boolean',
            'pay_on_arrival_allowed' => 'boolean',
            'all_fees_included' => 'boolean',
            'show_total_price_upfront' => 'boolean',
            'hidden_fees_disclosed' => 'boolean',
            'guest_service_fee_value' => 'decimal:2',
            'host_service_fee_value' => 'decimal:2',
            'tax_fee_value' => 'decimal:2',
            'city_fee_value' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    /**
     * Links these pricing settings to the Sleeping Place they price.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
