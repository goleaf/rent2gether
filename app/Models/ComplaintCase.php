<?php

namespace App\Models;

use Database\Factories\ComplaintCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintCase extends Model
{
    /** @use HasFactory<ComplaintCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_number',
        'booking_id',
        'booking_stay_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'guest_user_id',
        'host_user_id',
        'reporter_user_id',
        'against_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'source_type',
        'source_id',
        'submitted_by_type',
        'against_type',
        'complaint_type',
        'severity',
        'status',
        'title',
        'description',
        'desired_resolution_type',
        'resolution_type',
        'resolution_status',
        'guest_wants_refund',
        'guest_wants_relocation',
        'guest_wants_cancellation',
        'guest_wants_compensation',
        'host_wants_deposit_deduction',
        'host_wants_guest_warning_future',
        'host_wants_payment_resolution',
        'amount_requested',
        'currency',
        'other_party_notified_at',
        'other_party_responded_at',
        'resolved_at',
        'closed_at',
        'booking_refund_id',
        'booking_relocation_id',
        'booking_cancellation_id',
        'deposit_case_id',
        'maintenance_request_id',
        'cleaning_task_id',
        'has_dispute',
        'dispute_case_id',
        'future_review_required',
        'future_review_comment',
    ];

    protected $attributes = [
        'severity' => 'medium',
        'status' => 'submitted',
        'guest_wants_refund' => false,
        'guest_wants_relocation' => false,
        'guest_wants_cancellation' => false,
        'guest_wants_compensation' => false,
        'host_wants_deposit_deduction' => false,
        'host_wants_guest_warning_future' => false,
        'host_wants_payment_resolution' => false,
        'amount_requested' => 0,
        'has_dispute' => false,
        'future_review_required' => false,
    ];

    protected function casts(): array
    {
        return [
            'guest_wants_refund' => 'boolean',
            'guest_wants_relocation' => 'boolean',
            'guest_wants_cancellation' => 'boolean',
            'guest_wants_compensation' => 'boolean',
            'host_wants_deposit_deduction' => 'boolean',
            'host_wants_guest_warning_future' => 'boolean',
            'host_wants_payment_resolution' => 'boolean',
            'amount_requested' => 'decimal:2',
            'other_party_notified_at' => 'datetime',
            'other_party_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'has_dispute' => 'boolean',
            'future_review_required' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function against(): BelongsTo
    {
        return $this->belongsTo(User::class, 'against_user_id');
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

    public function disputeCase(): BelongsTo
    {
        return $this->belongsTo(DisputeCase::class, 'dispute_case_id');
    }

    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    public function bookingRelocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class);
    }

    public function bookingCancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class);
    }

    public function parties(): HasMany
    {
        return $this->hasMany(ComplaintParty::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ComplaintEvidence::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ComplaintResponse::class);
    }

    public function resolutionOptions(): HasMany
    {
        return $this->hasMany(ComplaintResolutionOption::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ComplaintAction::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ComplaintStatusLog::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ComplaintEvent::class);
    }
}
