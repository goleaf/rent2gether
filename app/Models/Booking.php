<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'bed_id',
        'guest_id',
        'guest_user_id',
        'host_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_type',
        'check_in',
        'check_out',
        'check_in_date',
        'check_out_date',
        'check_in_time',
        'check_out_time',
        'arrival_time',
        'guests_count',
        'nights',
        'nights_count',
        'calendar_days_count',
        'price_per_night',
        'subtotal',
        'subtotal_amount',
        'discount_amount',
        'cleaning_fee',
        'cleaning_fee_amount',
        'deposit',
        'deposit_amount',
        'service_fee',
        'service_fee_amount',
        'tax_amount',
        'city_fee_amount',
        'total',
        'total_amount',
        'refundable_amount',
        'non_refundable_amount',
        'currency',
        'status',
        'payment_status',
        'payment_method',
        'payment_paid_at',
        'payment_deadline_at',
        'availability_hold_expires_at',
        'requires_document_check',
        'requires_phone_check',
        'requires_identity_check',
        'cancellation_policy',
        'refund_amount',
        'refund_status',
        'cancel_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'cancellation_terms',
        'guest_message',
        'rules_accepted_at',
        'host_reply',
        'host_response',
        'check_in_instructions',
        'guest_checked_in_at',
        'guest_checked_out_at',
        'host_confirmed_checkin_at',
        'host_confirmed_checkout_at',
        'checked_in_at',
        'checked_out_at',
        'free_cancel_before',
        'deposit_released_at',
        'has_dispute',
        'has_complaint',
        'guest_review_left',
        'host_review_left',
        'review_deadline_at',
    ];

    /**
     * Defines how Laravel converts stored Booking attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'booking_type' => BookingType::class,
            'payment_status' => PaymentStatus::class,
            'cancellation_policy' => CancellationPolicy::class,
            'check_in' => 'date',
            'check_out' => 'date',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'check_in_time' => 'datetime:H:i',
            'check_out_time' => 'datetime:H:i',
            'arrival_time' => 'datetime:H:i',
            'price_per_night' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'cleaning_fee_amount' => 'decimal:2',
            'deposit' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'service_fee_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'city_fee_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'refundable_amount' => 'decimal:2',
            'non_refundable_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'requires_document_check' => 'boolean',
            'requires_phone_check' => 'boolean',
            'requires_identity_check' => 'boolean',
            'has_dispute' => 'boolean',
            'has_complaint' => 'boolean',
            'guest_review_left' => 'boolean',
            'host_review_left' => 'boolean',
            'review_deadline_at' => 'datetime',
            'guest_checked_in_at' => 'datetime',
            'guest_checked_out_at' => 'datetime',
            'host_confirmed_checkin_at' => 'datetime',
            'host_confirmed_checkout_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'free_cancel_before' => 'datetime',
            'deposit_released_at' => 'datetime',
            'payment_paid_at' => 'datetime',
            'payment_deadline_at' => 'datetime',
            'availability_hold_expires_at' => 'datetime',
            'rules_accepted_at' => 'datetime',
        ];
    }

    /**
     * Registers lifecycle hooks that keep Booking records consistent.
     */
    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            if (empty($booking->reference)) {
                $booking->reference = strtoupper('RTG-'.now()->format('ymd').'-'.substr(uniqid(), -5));
            }
        });

        static::saving(function (Booking $booking): void {
            $booking->guest_user_id ??= $booking->guest_id;
            $booking->guest_id ??= $booking->guest_user_id;
            $booking->host_user_id ??= $booking->host_id;
            $booking->host_id ??= $booking->host_user_id;
            $booking->check_in_date ??= $booking->check_in;
            $booking->check_out_date ??= $booking->check_out;
            $booking->check_in ??= $booking->check_in_date;
            $booking->check_out ??= $booking->check_out_date;
            $booking->nights_count = $booking->nights_count ?: $booking->nights;
            $booking->nights = $booking->nights ?: $booking->nights_count;
            $booking->subtotal_amount = $booking->subtotal_amount ?: $booking->subtotal;
            $booking->subtotal = $booking->subtotal ?: $booking->subtotal_amount;
            $booking->cleaning_fee_amount = $booking->cleaning_fee_amount ?: $booking->cleaning_fee;
            $booking->cleaning_fee = $booking->cleaning_fee ?: $booking->cleaning_fee_amount;
            $booking->deposit_amount = $booking->deposit_amount ?: $booking->deposit;
            $booking->deposit = $booking->deposit ?: $booking->deposit_amount;
            $booking->service_fee_amount = $booking->service_fee_amount ?: $booking->service_fee;
            $booking->service_fee = $booking->service_fee ?: $booking->service_fee_amount;
            $booking->total_amount = $booking->total_amount ?: $booking->total;
            $booking->total = $booking->total ?: $booking->total_amount;
            $booking->host_response ??= $booking->host_reply;
            $booking->host_reply ??= $booking->host_response;
            $booking->cancellation_reason ??= $booking->cancel_reason;
            $booking->cancel_reason ??= $booking->cancellation_reason;
            $booking->checked_in_at ??= $booking->guest_checked_in_at;
            $booking->checked_out_at ??= $booking->guest_checked_out_at;
        });
    }

    /**
     * Links this Booking to the Bed record used by its bed relation.
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    /**
     * Links this Booking to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this Booking to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Booking to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Booking to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Booking to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Lists related Booking Guest records for this Booking.
     */
    public function bookingGuests(): HasMany
    {
        return $this->hasMany(BookingGuest::class);
    }

    /**
     * Lists related Booking Price Line records for this Booking.
     */
    public function priceLines(): HasMany
    {
        return $this->hasMany(BookingPriceLine::class);
    }

    /**
     * Fetches the immutable price snapshot copied from the accepted Quote.
     */
    public function priceSnapshot(): HasOne
    {
        return $this->hasOne(BookingPriceSnapshot::class);
    }

    /**
     * Fetches the Booking Request that was converted into this Booking.
     */
    public function bookingRequest(): HasOne
    {
        return $this->hasOne(BookingRequest::class);
    }

    /**
     * Lists promo code redemptions finalized through this Booking.
     */
    public function promoCodeRedemptions(): HasMany
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    /**
     * Lists related Booking Status History records for this Booking.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    /**
     * Lists active and historical sleeping-place date locks created for this Booking.
     */
    public function sleepingPlaceDateLocks(): HasMany
    {
        return $this->hasMany(SleepingPlaceBookingDateLock::class);
    }

    /**
     * Lists reminder and deadline timeline dates copied from a Quote or built for this Booking.
     */
    public function timelineDates(): HasMany
    {
        return $this->hasMany(BookingTimelineDate::class);
    }

    /**
     * Fetches the single Booking Guest Intake record used by this Booking.
     */
    public function guestIntake(): HasOne
    {
        return $this->hasOne(BookingGuestIntake::class);
    }

    /**
     * Lists related Payment Record records for this Booking.
     */
    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class);
    }

    /**
     * Lists related Deposit Record records for this Booking.
     */
    public function depositRecords(): HasMany
    {
        return $this->hasMany(DepositRecord::class);
    }

    /**
     * Lists related Refund Request records for this Booking.
     */
    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    /**
     * Adds the active query filter for reusable Booking lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
        ]);
    }

    /**
     * Adds the upcoming query filter for reusable Booking lookups.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('check_in_date', '>=', now()->toDateString())
            ->whereNotIn('status', [
                BookingStatus::CancelledByGuest->value,
                BookingStatus::CancelledByGuestFlow->value,
                BookingStatus::CancelledByHost->value,
                BookingStatus::CancelledByHostFlow->value,
                BookingStatus::CancelledBySystem->value,
                BookingStatus::DeclinedByHost->value,
                BookingStatus::Expired->value,
                BookingStatus::NoShow->value,
            ]);
    }

    /**
     * Adds the for host query filter for reusable Booking lookups.
     */
    public function scopeForHost(Builder $query, int $userId): Builder
    {
        return $query->where('host_user_id', $userId);
    }

    /**
     * Adds the for guest query filter for reusable Booking lookups.
     */
    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->where('guest_user_id', $userId);
    }

    /**
     * Checks whether this Booking is still in a cancellable state.
     */
    public function isCancellable(): bool
    {
        return ! $this->status->isCancelled()
            && ! in_array($this->status, [BookingStatus::CheckedIn, BookingStatus::InProgress, BookingStatus::ActiveStay, BookingStatus::Completed], true);
    }

    /**
     * Checks whether this Booking can still be cancelled for free.
     */
    public function canCancelFree(): bool
    {
        return $this->free_cancel_before && now()->lt($this->free_cancel_before);
    }

    /**
     * Lists related Review records for this Booking.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Lists related Booking Extension records for this Booking.
     */
    public function extensions(): HasMany
    {
        return $this->hasMany(BookingExtension::class);
    }

    /**
     * Fetches the single Checkin Record record used by this Booking.
     */
    public function checkinRecord(): HasOne
    {
        return $this->hasOne(CheckinRecord::class);
    }

    /**
     * Fetches the single Booking Check In record used by this Booking.
     */
    public function checkIn(): HasOne
    {
        return $this->hasOne(BookingCheckIn::class);
    }

    /**
     * Fetches the single Checkout Record record used by this Booking.
     */
    public function bookingCheckIn(): HasOne
    {
        return $this->checkIn();
    }

    /**
     * Fetches the single Checkout Record record used by this Booking.
     */
    public function checkoutRecord(): HasOne
    {
        return $this->hasOne(CheckoutRecord::class);
    }

    /**
     * Fetches the single Booking Check Out record used by this Booking.
     */
    public function checkOut(): HasOne
    {
        return $this->hasOne(BookingCheckOut::class);
    }

    /**
     * Fetches the single Complaint record used by this Booking.
     */
    public function bookingCheckOut(): HasOne
    {
        return $this->checkOut();
    }

    /**
     * Lists related Complaint records for this Booking.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Fetches the single Payout record used by this Booking.
     */
    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }

    /**
     * Fetches the single Room Occupant Snapshot record used by this Booking.
     */
    public function occupantSnapshot(): HasOne
    {
        return $this->hasOne(RoomOccupantSnapshot::class);
    }

    /**
     * Lists related Host Calendar Event records for this Booking.
     */
    public function hostCalendarEvents(): HasMany
    {
        return $this->hasMany(HostCalendarEvent::class);
    }

    /**
     * Lists related Host Calendar Note records for this Booking.
     */
    public function hostCalendarNotes(): HasMany
    {
        return $this->hasMany(HostCalendarNote::class);
    }

    /**
     * Fetches the single Host Current Stay Snapshot record used by this Booking.
     */
    public function hostCurrentStaySnapshot(): HasOne
    {
        return $this->hasOne(HostCurrentStaySnapshot::class);
    }

    /**
     * Lists related Host Guest Stay Note records for this Booking.
     */
    public function hostGuestStayNotes(): HasMany
    {
        return $this->hasMany(HostGuestStayNote::class);
    }

    /**
     * Lists related Host Guest Stay Flag records for this Booking.
     */
    public function hostGuestStayFlags(): HasMany
    {
        return $this->hasMany(HostGuestStayFlag::class);
    }

    /**
     * Lists related Booking Review Request records for this Booking.
     */
    public function reviewRequests(): HasMany
    {
        return $this->hasMany(BookingReviewRequest::class);
    }

    /**
     * Returns the guest-to-place review written for this Booking.
     */
    public function guestReview(): ?Review
    {
        return $this->reviews()->where('type', 'guest_to_place')->first();
    }

    /**
     * Returns the host-to-guest review written for this Booking.
     */
    public function hostReview(): ?Review
    {
        return $this->reviews()->where('type', 'host_to_guest')->first();
    }
}
