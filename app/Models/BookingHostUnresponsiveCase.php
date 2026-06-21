<?php

namespace App\Models;

use Database\Factories\BookingHostUnresponsiveCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingHostUnresponsiveCase extends Model
{
    /** @use HasFactory<BookingHostUnresponsiveCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'case_number',
        'booking_id',
        'booking_check_in_id',
        'booking_stay_id',
        'guest_user_id',
        'host_user_id',
        'host_representative_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'case_type',
        'reason_key',
        'status',
        'check_in_date',
        'planned_check_in_time',
        'check_in_window',
        'actual_guest_arrival_at',
        'guest_marked_arrived',
        'guest_waiting_outside',
        'guest_at_address',
        'guest_feels_unsafe',
        'instruction_was_available',
        'exact_address_was_shown',
        'door_code_was_shown',
        'intercom_code_was_shown',
        'key_safe_code_was_shown',
        'host_contact_was_shown',
        'representative_contact_was_shown',
        'host_contact_attempts_count',
        'representative_contact_attempts_count',
        'last_host_contact_attempt_at',
        'last_representative_contact_attempt_at',
        'host_last_response_at',
        'representative_last_response_at',
        'response_deadline_minutes',
        'response_deadline_at',
        'response_deadline_expired_at',
        'guest_wants_help',
        'guest_wants_cancellation',
        'guest_wants_refund',
        'guest_wants_relocation',
        'host_response',
        'representative_response',
        'guest_comment',
        'host_comment',
        'decision_key',
        'decision_at',
        'decided_by_user_id',
        'refund_status',
        'refund_amount',
        'compensation_amount_future',
        'currency',
        'booking_cancellation_id',
        'booking_relocation_id',
        'complaint_case_id',
        'check_in_problem_id',
        'booking_refund_id',
        'future_support_review_required',
        'future_support_comment',
        'resolved_at',
        'closed_at',
    ];

    protected $hidden = [
        'future_support_comment',
    ];

    protected $attributes = [
        'status' => 'reported',
        'guest_marked_arrived' => false,
        'guest_waiting_outside' => false,
        'guest_at_address' => false,
        'guest_feels_unsafe' => false,
        'instruction_was_available' => false,
        'exact_address_was_shown' => false,
        'door_code_was_shown' => false,
        'intercom_code_was_shown' => false,
        'key_safe_code_was_shown' => false,
        'host_contact_was_shown' => false,
        'representative_contact_was_shown' => false,
        'host_contact_attempts_count' => 0,
        'representative_contact_attempts_count' => 0,
        'response_deadline_minutes' => 60,
        'guest_wants_help' => true,
        'guest_wants_cancellation' => false,
        'guest_wants_refund' => false,
        'guest_wants_relocation' => false,
        'refund_amount' => 0,
        'compensation_amount_future' => 0,
        'future_support_review_required' => false,
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'actual_guest_arrival_at' => 'datetime',
            'guest_marked_arrived' => 'boolean',
            'guest_waiting_outside' => 'boolean',
            'guest_at_address' => 'boolean',
            'guest_feels_unsafe' => 'boolean',
            'instruction_was_available' => 'boolean',
            'exact_address_was_shown' => 'boolean',
            'door_code_was_shown' => 'boolean',
            'intercom_code_was_shown' => 'boolean',
            'key_safe_code_was_shown' => 'boolean',
            'host_contact_was_shown' => 'boolean',
            'representative_contact_was_shown' => 'boolean',
            'host_contact_attempts_count' => 'integer',
            'representative_contact_attempts_count' => 'integer',
            'last_host_contact_attempt_at' => 'datetime',
            'last_representative_contact_attempt_at' => 'datetime',
            'host_last_response_at' => 'datetime',
            'representative_last_response_at' => 'datetime',
            'response_deadline_minutes' => 'integer',
            'response_deadline_at' => 'datetime',
            'response_deadline_expired_at' => 'datetime',
            'guest_wants_help' => 'boolean',
            'guest_wants_cancellation' => 'boolean',
            'guest_wants_refund' => 'boolean',
            'guest_wants_relocation' => 'boolean',
            'decision_at' => 'datetime',
            'refund_amount' => 'decimal:2',
            'compensation_amount_future' => 'decimal:2',
            'future_support_review_required' => 'boolean',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    public function bookingStay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function hostRepresentative(): BelongsTo
    {
        return $this->belongsTo(HostRepresentative::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    public function bookingCancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class);
    }

    public function bookingRelocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class);
    }

    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    public function contactAttempts(): HasMany
    {
        return $this->hasMany(HostUnresponsiveContactAttempt::class, 'host_unresponsive_case_id');
    }

    public function guestActions(): HasMany
    {
        return $this->hasMany(HostUnresponsiveGuestAction::class, 'host_unresponsive_case_id');
    }

    public function hostResponses(): HasMany
    {
        return $this->hasMany(HostUnresponsiveHostResponse::class, 'host_unresponsive_case_id');
    }

    public function representativeResponses(): HasMany
    {
        return $this->hasMany(HostUnresponsiveRepresentativeResponse::class, 'host_unresponsive_case_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(HostUnresponsiveMedia::class, 'host_unresponsive_case_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(HostUnresponsiveStatusLog::class, 'host_unresponsive_case_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(HostUnresponsiveEvent::class, 'host_unresponsive_case_id');
    }
}
