<?php

namespace App\Models;

use Database\Factories\PromoCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    public const TYPE_PROMO_CODE = 'promo_code';

    public const VALUE_PERCENT = 'percent';

    public const VALUE_FIXED_AMOUNT = 'fixed_amount';

    public const VALUE_FIXED_PRICE = 'fixed_price';

    /** @use HasFactory<PromoCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'value_type',
        'value',
        'currency',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_limit_per_user',
        'min_booking_amount',
        'min_nights',
        'new_guest_only',
        'sleeping_place_id',
        'property_id',
        'host_user_id',
        'active',
    ];

    /**
     * Defines how Laravel converts stored promo code attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'min_booking_amount' => 'decimal:2',
            'min_nights' => 'integer',
            'new_guest_only' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Normalizes promo code values before saving.
     */
    protected static function booted(): void
    {
        static::saving(function (PromoCode $promoCode): void {
            $promoCode->code = strtoupper(trim((string) $promoCode->code));
        });
    }

    /**
     * Adds the reusable active filter for promo code lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Links this promo code to a specific Sleeping Place when scoped that way.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this promo code to a specific Property when scoped that way.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this promo code to the host owner when scoped that way.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Lists all redemptions recorded for this promo code.
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }
}
