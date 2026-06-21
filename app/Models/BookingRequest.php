<?php

namespace App\Models;

use Database\Factories\BookingRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingRequest extends Model
{
    public const TYPE_HOST_APPROVAL = 'host_approval';

    public const TYPE_STAY_REQUEST = 'stay_request';

    public const TYPE_PRELIMINARY_INQUIRY = 'preliminary_inquiry';

    public const TYPE_LONG_TERM_REQUEST = 'long_term_request';

    public const TYPE_SAME_DAY_URGENT = 'same_day_urgent';

    public const TYPE_REQUEST_ONLY = 'request_only';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_HOST_SEEN = 'host_seen';

    public const STATUS_WAITING_HOST_RESPONSE = 'waiting_host_response';

    public const STATUS_WAITING_GUEST_RESPONSE = 'waiting_guest_response';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_APPROVED_WAITING_PAYMENT = 'approved_waiting_payment';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_WITHDRAWN_BY_GUEST = 'withdrawn_by_guest';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_CONVERTED_TO_BOOKING = 'converted_to_booking';

    public const STATUS_DATES_UNAVAILABLE = 'dates_unavailable';

    /** @use HasFactory<BookingRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'request_number',
        'booking_quote_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'request_type',
        'status',
        'hold_dates',
        'hold_expires_at',
        'expires_at',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'nights_count',
        'chargeable_days_count',
        'calendar_presence_days_count',
        'guests_count',
        'trip_purpose',
        'planned_arrival_time',
        'planned_departure_time',
        'guest_message',
        'has_baggage',
        'needs_luggage_storage',
        'needs_early_check_in',
        'needs_late_checkout',
        'needs_residence_registration',
        'needs_reporting_documents',
        'guest_agreed_to_rules',
        'guest_agreed_to_cancellation_policy',
        'guest_agreed_to_deposit_policy',
        'guest_profile_snapshot_json',
        'guest_rating_snapshot_json',
        'compatibility_snapshot_json',
        'price_snapshot_json',
        'warnings_snapshot_json',
        'total_amount',
        'deposit_amount',
        'cleaning_fee_amount',
        'service_fee_amount',
        'currency',
        'host_response',
        'rejection_reason',
        'rejected_at',
        'approved_at',
        'converted_to_booking_at',
    ];

    protected $attributes = [
        'status' => self::STATUS_SUBMITTED,
        'hold_dates' => false,
        'has_baggage' => false,
        'needs_luggage_storage' => false,
        'needs_early_check_in' => false,
        'needs_late_checkout' => false,
        'needs_residence_registration' => false,
        'needs_reporting_documents' => false,
        'guest_agreed_to_rules' => false,
        'guest_agreed_to_cancellation_policy' => false,
        'guest_agreed_to_deposit_policy' => false,
        'currency' => 'EUR',
    ];

    /**
     * Defines how Laravel converts stored Booking Request attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'hold_dates' => 'boolean',
            'hold_expires_at' => 'datetime',
            'expires_at' => 'datetime',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'nights_count' => 'integer',
            'chargeable_days_count' => 'integer',
            'calendar_presence_days_count' => 'integer',
            'guests_count' => 'integer',
            'has_baggage' => 'boolean',
            'needs_luggage_storage' => 'boolean',
            'needs_early_check_in' => 'boolean',
            'needs_late_checkout' => 'boolean',
            'needs_residence_registration' => 'boolean',
            'needs_reporting_documents' => 'boolean',
            'guest_agreed_to_rules' => 'boolean',
            'guest_agreed_to_cancellation_policy' => 'boolean',
            'guest_agreed_to_deposit_policy' => 'boolean',
            'guest_profile_snapshot_json' => 'array',
            'guest_rating_snapshot_json' => 'array',
            'compatibility_snapshot_json' => 'array',
            'price_snapshot_json' => 'array',
            'warnings_snapshot_json' => 'array',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'cleaning_fee_amount' => 'decimal:2',
            'service_fee_amount' => 'decimal:2',
            'rejected_at' => 'datetime',
            'approved_at' => 'datetime',
            'converted_to_booking_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Request to the source Quote when one exists.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
    }

    /**
     * Links this Booking Request to the confirmed Booking created after approval.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Request to the guest who sent it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Booking Request to the host who must respond.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Booking Request to the Property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Booking Request to the Room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Booking Request to the requested Sleeping Place.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists warning rows generated for host decision support.
     */
    public function warnings(): HasMany
    {
        return $this->hasMany(BookingRequestWarning::class);
    }

    /**
     * Lists host responses attached to this Booking Request.
     */
    public function hostResponses(): HasMany
    {
        return $this->hasMany(BookingRequestHostResponse::class);
    }

    /**
     * Lists guest responses attached to this Booking Request.
     */
    public function guestResponses(): HasMany
    {
        return $this->hasMany(BookingRequestGuestResponse::class);
    }

    /**
     * Lists status log rows for this Booking Request.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingRequestStatusLog::class);
    }

    /**
     * Lists rule compatibility rows generated for this Booking Request.
     */
    public function compatibilityResults(): HasMany
    {
        return $this->hasMany(BookingRequestCompatibilityResult::class);
    }

    /**
     * Lists date locks currently or historically created by this Booking Request.
     */
    public function dateLocks(): HasMany
    {
        return $this->hasMany(SleepingPlaceBookingDateLock::class);
    }

    /**
     * Adds the host/status filter used by mobile host request lists.
     */
    public function scopeForHost(Builder $query, User|int $host): Builder
    {
        $hostId = $host instanceof User ? $host->id : $host;

        return $query->where('host_user_id', $hostId);
    }

    /**
     * Adds the guest/status filter used by mobile guest request lists.
     */
    public function scopeForGuest(Builder $query, User|int $guest): Builder
    {
        $guestId = $guest instanceof User ? $guest->id : $guest;

        return $query->where('guest_user_id', $guestId);
    }
}
