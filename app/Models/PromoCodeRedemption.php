<?php

namespace App\Models;

use Database\Factories\PromoCodeRedemptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCodeRedemption extends Model
{
    /** @use HasFactory<PromoCodeRedemptionFactory> */
    use HasFactory;

    protected $fillable = [
        'promo_code_id',
        'user_id',
        'booking_quote_id',
        'booking_id',
        'discount_amount',
        'currency',
        'redeemed_at',
    ];

    /**
     * Defines how Laravel converts stored promo redemption attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'redeemed_at' => 'datetime',
        ];
    }

    /**
     * Links this redemption to its promo code.
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * Links this redemption to the guest who used the code.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this redemption to the quote context when it was previewed.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
    }

    /**
     * Links this redemption to the booking that finalized the promo use.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
