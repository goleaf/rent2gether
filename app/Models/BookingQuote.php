<?php

namespace App\Models;

use Database\Factories\BookingQuoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingQuote extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_VALID = 'valid';

    public const STATUS_INVALID = 'invalid';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CONVERTED_TO_BOOKING = 'converted_to_booking';

    public const STATUS_CONVERTED_TO_REQUEST = 'converted_to_request';

    public const STATUS_CANCELLED = 'cancelled';

    /** @use HasFactory<BookingQuoteFactory> */
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'user_id',
        'sleeping_place_id',
        'room_id',
        'property_id',
        'host_user_id',
        'rental_mode',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'check_in_window',
        'check_out_window',
        'nights_count',
        'chargeable_days_count',
        'calendar_presence_days_count',
        'guests_count',
        'included_guests_count',
        'extra_guests_count',
        'early_check_in_requested',
        'late_check_out_requested',
        'flexible_check_in',
        'flexible_check_out',
        'requires_host_time_approval',
        'check_in_comment',
        'check_out_comment',
        'availability_status',
        'validation_status',
        'pricing_status',
        'accommodation_amount',
        'discount_amount',
        'cleaning_fee_amount',
        'service_fee_amount',
        'tax_amount',
        'city_fee_amount',
        'deposit_amount',
        'total_without_deposit',
        'total_payable',
        'host_payout_preview_amount',
        'refundable_amount',
        'non_refundable_amount',
        'currency',
        'free_cancellation_until',
        'cancellation_penalty_starts_at',
        'payment_deadline_at',
        'host_payout_due_at',
        'guest_check_in_reminder_at',
        'guest_check_out_reminder_at',
        'host_check_in_reminder_at',
        'host_check_out_reminder_at',
        'deposit_review_start_at',
        'review_request_at',
        'promo_code',
        'promo_code_status',
        'expires_at',
        'status',
    ];

    /**
     * Defines how Laravel converts stored Booking Quote attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'nights_count' => 'integer',
            'chargeable_days_count' => 'integer',
            'calendar_presence_days_count' => 'integer',
            'guests_count' => 'integer',
            'included_guests_count' => 'integer',
            'extra_guests_count' => 'integer',
            'early_check_in_requested' => 'boolean',
            'late_check_out_requested' => 'boolean',
            'flexible_check_in' => 'boolean',
            'flexible_check_out' => 'boolean',
            'requires_host_time_approval' => 'boolean',
            'accommodation_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'cleaning_fee_amount' => 'decimal:2',
            'service_fee_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'city_fee_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'total_without_deposit' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'host_payout_preview_amount' => 'decimal:2',
            'refundable_amount' => 'decimal:2',
            'non_refundable_amount' => 'decimal:2',
            'free_cancellation_until' => 'datetime',
            'cancellation_penalty_starts_at' => 'datetime',
            'payment_deadline_at' => 'datetime',
            'host_payout_due_at' => 'datetime',
            'guest_check_in_reminder_at' => 'datetime',
            'guest_check_out_reminder_at' => 'datetime',
            'host_check_in_reminder_at' => 'datetime',
            'host_check_out_reminder_at' => 'datetime',
            'deposit_review_start_at' => 'datetime',
            'review_request_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Links this Quote to the guest user who requested the calculation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Links this Quote to the host user who owns the sleeping place.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Quote to the Sleeping Place being priced and checked.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this Quote to the Room context for the sleeping place.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Quote to the Property context for the sleeping place.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Lists the transparent price lines generated for this Quote.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(BookingQuoteLine::class);
    }

    /**
     * Lists validation messages generated while checking this Quote.
     */
    public function validationResults(): HasMany
    {
        return $this->hasMany(BookingQuoteValidationResult::class);
    }

    /**
     * Lists reminder and deadline dates calculated for this Quote.
     */
    public function timelineDates(): HasMany
    {
        return $this->hasMany(BookingTimelineDate::class);
    }

    /**
     * Lists alternative dates and places suggested for this Quote.
     */
    public function suggestions(): HasMany
    {
        return $this->hasMany(BookingQuoteSuggestion::class);
    }

    /**
     * Lists booking requests submitted from this preliminary Quote.
     */
    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    /**
     * Lists promo code redemptions previewed or finalized from this Quote.
     */
    public function promoCodeRedemptions(): HasMany
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }
}
