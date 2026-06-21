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
        'booking_number',
        'booking_quote_id',
        'booking_request_id',
        'reference',
        'bed_id',
        'guest_id',
        'guest_user_id',
        'host_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'group_booking_id',
        'parent_booking_id',
        'extension_from_booking_id',
        'relocation_from_booking_id',
        'booking_type',
        'approval_type',
        'payment_type',
        'deposit_mode',
        'guest_group_type',
        'source_type',
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
        'chargeable_days_count',
        'calendar_days_count',
        'calendar_presence_days_count',
        'included_guests_count',
        'extra_guests_count',
        'price_per_night',
        'nightly_price_snapshot',
        'subtotal',
        'subtotal_amount',
        'accommodation_amount',
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
        'total_without_deposit',
        'total_payable',
        'host_payout_amount',
        'refundable_amount',
        'non_refundable_amount',
        'currency',
        'status',
        'payment_status',
        'payment_method',
        'payment_paid_at',
        'paid_at',
        'payment_deadline_at',
        'availability_hold_expires_at',
        'requires_document_check',
        'requires_phone_check',
        'requires_identity_check',
        'requires_phone_verification',
        'requires_identity_verification',
        'requires_document_verification',
        'verification_status',
        'phone_verified_at',
        'identity_verified_at',
        'documents_verified_at',
        'cancellation_policy',
        'refund_amount',
        'refund_status',
        'rejection_reason',
        'rejected_by_user_id',
        'rejected_at',
        'cancel_reason',
        'cancelled_by',
        'cancelled_by_user_id',
        'cancelled_by_type',
        'cancelled_at',
        'cancellation_reason',
        'cancellation_policy_snapshot_id',
        'cancellation_terms',
        'guest_message',
        'rules_accepted_at',
        'host_reply',
        'host_response',
        'check_in_instructions',
        'check_in_instruction_available',
        'guest_checked_in_at',
        'guest_checked_out_at',
        'host_confirmed_checkin_at',
        'host_confirmed_checkout_at',
        'guest_check_in_confirmed_at',
        'host_check_in_confirmed_at',
        'guest_check_out_confirmed_at',
        'host_check_out_confirmed_at',
        'checked_in_at',
        'checked_out_at',
        'stay_started_at',
        'stay_ended_at',
        'free_cancel_before',
        'deposit_released_at',
        'has_dispute',
        'has_complaint',
        'has_open_maintenance',
        'has_deposit_issue',
        'guest_review_left',
        'host_review_left',
        'guest_review_left_at',
        'host_review_left_at',
        'review_deadline_at',
        'closed_at',
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
            'nights' => 'integer',
            'nights_count' => 'integer',
            'chargeable_days_count' => 'integer',
            'calendar_days_count' => 'integer',
            'calendar_presence_days_count' => 'integer',
            'guests_count' => 'integer',
            'included_guests_count' => 'integer',
            'extra_guests_count' => 'integer',
            'nightly_price_snapshot' => 'array',
            'price_per_night' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'subtotal_amount' => 'decimal:2',
            'accommodation_amount' => 'decimal:2',
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
            'total_without_deposit' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'host_payout_amount' => 'decimal:2',
            'refundable_amount' => 'decimal:2',
            'non_refundable_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'requires_document_check' => 'boolean',
            'requires_phone_check' => 'boolean',
            'requires_identity_check' => 'boolean',
            'requires_phone_verification' => 'boolean',
            'requires_identity_verification' => 'boolean',
            'requires_document_verification' => 'boolean',
            'check_in_instruction_available' => 'boolean',
            'has_dispute' => 'boolean',
            'has_complaint' => 'boolean',
            'has_open_maintenance' => 'boolean',
            'has_deposit_issue' => 'boolean',
            'guest_review_left' => 'boolean',
            'host_review_left' => 'boolean',
            'review_deadline_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'identity_verified_at' => 'datetime',
            'documents_verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'guest_checked_in_at' => 'datetime',
            'guest_checked_out_at' => 'datetime',
            'host_confirmed_checkin_at' => 'datetime',
            'host_confirmed_checkout_at' => 'datetime',
            'guest_check_in_confirmed_at' => 'datetime',
            'host_check_in_confirmed_at' => 'datetime',
            'guest_check_out_confirmed_at' => 'datetime',
            'host_check_out_confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'stay_started_at' => 'datetime',
            'stay_ended_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'paid_at' => 'datetime',
            'free_cancel_before' => 'datetime',
            'deposit_released_at' => 'datetime',
            'payment_paid_at' => 'datetime',
            'payment_deadline_at' => 'datetime',
            'availability_hold_expires_at' => 'datetime',
            'rules_accepted_at' => 'datetime',
            'guest_review_left_at' => 'datetime',
            'host_review_left_at' => 'datetime',
            'closed_at' => 'datetime',
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
            $booking->syncAliasPair('guest_user_id', 'guest_id');
            $booking->syncAliasPair('host_user_id', 'host_id');
            $booking->syncAliasPair('check_in_date', 'check_in');
            $booking->syncAliasPair('check_out_date', 'check_out');
            $booking->syncCountAliasPair('nights_count', 'nights');
            $booking->fillMissingCountFrom('chargeable_days_count', 'nights_count');
            $booking->syncCountAliasPair('calendar_presence_days_count', 'calendar_days_count');
            $booking->syncAliasPair('subtotal_amount', 'subtotal');
            $booking->fillMissingFrom('accommodation_amount', 'subtotal_amount');
            $booking->syncAliasPair('cleaning_fee_amount', 'cleaning_fee');
            $booking->syncAliasPair('deposit_amount', 'deposit');
            $booking->syncAliasPair('service_fee_amount', 'service_fee');
            $booking->syncAliasPair('total_amount', 'total');
            $booking->fillMissingFrom('total_payable', 'total_amount');
            $booking->fillMissingTotalWithoutDeposit();
            $booking->syncAliasPair('paid_at', 'payment_paid_at');
            $booking->syncAliasPair('requires_phone_verification', 'requires_phone_check');
            $booking->syncAliasPair('requires_identity_verification', 'requires_identity_check');
            $booking->syncAliasPair('requires_document_verification', 'requires_document_check');
            $booking->syncAliasPair('host_response', 'host_reply');
            $booking->fillMissingFrom('rejection_reason', 'cancel_reason');
            $booking->syncAliasPair('cancellation_reason', 'cancel_reason');
            $booking->syncAliasPair('checked_in_at', 'guest_checked_in_at');
            $booking->syncAliasPair('checked_out_at', 'guest_checked_out_at');
            $booking->syncAliasPair('guest_check_in_confirmed_at', 'guest_checked_in_at');
            $booking->syncAliasPair('guest_check_out_confirmed_at', 'guest_checked_out_at');
            $booking->syncAliasPair('host_check_in_confirmed_at', 'host_confirmed_checkin_at');
            $booking->syncAliasPair('host_check_out_confirmed_at', 'host_confirmed_checkout_at');
        });
    }

    private function syncAliasPair(string $primary, string $alias): void
    {
        $this->fillMissingFrom($primary, $alias);
        $this->fillMissingFrom($alias, $primary);
    }

    private function syncCountAliasPair(string $primary, string $alias): void
    {
        $this->fillMissingCountFrom($primary, $alias);
        $this->fillMissingCountFrom($alias, $primary);
    }

    private function fillMissingFrom(string $target, string $source): void
    {
        $attributes = $this->getAttributes();

        if (! array_key_exists($source, $attributes) || $attributes[$source] === null) {
            return;
        }

        if (! array_key_exists($target, $attributes) || $attributes[$target] === null) {
            $this->setAttribute($target, $attributes[$source]);
        }
    }

    private function fillMissingCountFrom(string $target, string $source): void
    {
        $attributes = $this->getAttributes();

        if (! array_key_exists($source, $attributes) || $attributes[$source] === null || (int) $attributes[$source] <= 0) {
            return;
        }

        if (! array_key_exists($target, $attributes) || $attributes[$target] === null || (int) $attributes[$target] <= 0) {
            $this->setAttribute($target, $attributes[$source]);
        }
    }

    private function fillMissingTotalWithoutDeposit(): void
    {
        $attributes = $this->getAttributes();

        if (array_key_exists('total_without_deposit', $attributes) && $attributes['total_without_deposit'] !== null) {
            return;
        }

        if (! array_key_exists('total_payable', $attributes) || $attributes['total_payable'] === null) {
            return;
        }

        $deposit = array_key_exists('deposit_amount', $attributes) && $attributes['deposit_amount'] !== null
            ? (float) $attributes['deposit_amount']
            : 0.0;

        $this->setAttribute('total_without_deposit', max(0, (float) $attributes['total_payable'] - $deposit));
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
     * Links this Booking to the source Quote used to create it.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
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
     * Links this Booking to the Booking Request that approved it in the core flow.
     */
    public function sourceBookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class, 'booking_request_id');
    }

    /**
     * Links this Booking to its parent Booking for grouped child records.
     */
    public function parentBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    /**
     * Links this Booking to the Booking it extends.
     */
    public function extensionFromBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'extension_from_booking_id');
    }

    /**
     * Links this Booking to the Booking it relocated from.
     */
    public function relocationFromBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'relocation_from_booking_id');
    }

    /**
     * Lists relocation requests that start from this original Booking.
     */
    public function relocations(): HasMany
    {
        return $this->hasMany(BookingRelocation::class, 'original_booking_id');
    }

    /**
     * Lists new booking segments created after relocating from this Booking.
     */
    public function relocationSegments(): HasMany
    {
        return $this->hasMany(Booking::class, 'relocation_from_booking_id');
    }

    /**
     * Lists requirement records that can block lifecycle progress.
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(BookingRequirement::class);
    }

    /**
     * Lists host responses made directly on the Booking lifecycle.
     */
    public function hostResponses(): HasMany
    {
        return $this->hasMany(BookingHostResponse::class);
    }

    /**
     * Lists compact status log rows for the core Booking lifecycle.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    /**
     * Lists lifecycle events recorded for timeline and audit views.
     */
    public function lifecycleEvents(): HasMany
    {
        return $this->hasMany(BookingLifecycleEvent::class);
    }

    /**
     * Lists group links that connect this Booking to sibling sleeping-place bookings.
     */
    public function groupLinks(): HasMany
    {
        return $this->hasMany(BookingGroupLink::class);
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
     * Lists internal payment records created by the booking payment module.
     */
    public function bookingPayments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    /**
     * Lists payment deadlines attached to this Booking.
     */
    public function bookingPaymentDeadlines(): HasMany
    {
        return $this->hasMany(BookingPaymentDeadline::class);
    }

    /**
     * Lists refund records created for this Booking.
     */
    public function bookingRefunds(): HasMany
    {
        return $this->hasMany(BookingRefund::class);
    }

    /**
     * Fetches the immutable cancellation policy snapshot copied at booking time.
     */
    public function cancellationPolicySnapshot(): HasOne
    {
        return $this->hasOne(BookingCancellationPolicySnapshot::class);
    }

    /**
     * Lists refund previews calculated before final cancellation.
     */
    public function cancellationPreviews(): HasMany
    {
        return $this->hasMany(BookingCancellationPreview::class);
    }

    /**
     * Lists cancellation records created for this Booking.
     */
    public function cancellations(): HasMany
    {
        return $this->hasMany(BookingCancellation::class);
    }

    /**
     * Lists payment receipts issued for this Booking.
     */
    public function paymentReceipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
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
     * Fetches the active residence record created after successful check-in.
     */
    public function stay(): HasOne
    {
        return $this->hasOne(BookingStay::class);
    }

    /**
     * Fetches the active residence record using the module-specific relation name.
     */
    public function bookingStay(): HasOne
    {
        return $this->stay();
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
