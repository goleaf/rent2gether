<?php

namespace App\Models;

use Database\Factories\SleepingPlaceDiscountRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceDiscountRule extends Model
{
    public const TYPE_WEEKLY = 'weekly';

    public const TYPE_MONTHLY = 'monthly';

    public const TYPE_LONG_STAY = 'long_stay';

    public const TYPE_EARLY_BOOKING = 'early_booking';

    public const TYPE_LAST_MINUTE = 'last_minute';

    public const TYPE_NEW_GUEST = 'new_guest';

    public const TYPE_PERSONAL = 'personal';

    public const TYPE_MANUAL = 'manual';

    public const VALUE_PERCENT = 'percent';

    public const VALUE_FIXED_AMOUNT = 'fixed_amount';

    public const VALUE_FIXED_PRICE = 'fixed_price';

    /** @use HasFactory<SleepingPlaceDiscountRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'discount_type',
        'name',
        'value_type',
        'value',
        'min_nights',
        'max_nights',
        'min_days_before_check_in',
        'max_days_before_check_in',
        'new_guest_only',
        'allow_stacking',
        'priority',
        'active',
        'starts_at',
        'ends_at',
    ];

    /**
     * Defines how Laravel converts stored discount rule attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_nights' => 'integer',
            'max_nights' => 'integer',
            'min_days_before_check_in' => 'integer',
            'max_days_before_check_in' => 'integer',
            'new_guest_only' => 'boolean',
            'allow_stacking' => 'boolean',
            'priority' => 'integer',
            'active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Links this discount rule to the Sleeping Place it can discount.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
