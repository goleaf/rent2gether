<?php

namespace App\Models;

use Database\Factories\DisputeCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisputeCase extends Model
{
    /** @use HasFactory<DisputeCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'dispute_number',
        'complaint_case_id',
        'booking_id',
        'booking_stay_id',
        'guest_user_id',
        'host_user_id',
        'opened_by_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'source_type',
        'source_id',
        'dispute_type',
        'severity',
        'status',
        'title',
        'description',
        'amount_disputed',
        'currency',
        'booking_refund_id',
        'deposit_case_id',
        'booking_cancellation_id',
        'booking_relocation_id',
        'booking_no_show_id',
        'host_unresponsive_case_id',
        'mismatch_report_id',
        'booking_frozen',
        'refund_frozen',
        'deposit_frozen',
        'host_payout_frozen',
        'rating_impact_frozen',
        'proposed_resolution_type',
        'final_resolution_type',
        'final_resolution_note',
        'future_decision_required',
        'future_decision_comment',
        'future_decided_at',
        'resolved_at',
        'closed_at',
    ];

    protected $attributes = [
        'severity' => 'medium',
        'status' => 'opened',
        'amount_disputed' => 0,
        'booking_frozen' => false,
        'refund_frozen' => false,
        'deposit_frozen' => false,
        'host_payout_frozen' => false,
        'rating_impact_frozen' => false,
        'future_decision_required' => false,
    ];

    protected function casts(): array
    {
        return [
            'amount_disputed' => 'decimal:2',
            'booking_frozen' => 'boolean',
            'refund_frozen' => 'boolean',
            'deposit_frozen' => 'boolean',
            'host_payout_frozen' => 'boolean',
            'rating_impact_frozen' => 'boolean',
            'future_decision_required' => 'boolean',
            'future_decided_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function complaintCase(): BelongsTo
    {
        return $this->belongsTo(ComplaintCase::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
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

    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    public function bookingCancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class);
    }

    public function bookingRelocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class);
    }

    public function noShow(): BelongsTo
    {
        return $this->belongsTo(BookingNoShow::class, 'booking_no_show_id');
    }

    public function hostUnresponsiveCase(): BelongsTo
    {
        return $this->belongsTo(BookingHostUnresponsiveCase::class, 'host_unresponsive_case_id');
    }

    public function mismatchReport(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'mismatch_report_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(DisputeEvidence::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(DisputeResolutionProposal::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(DisputeDecision::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(DisputeStatusLog::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DisputeEvent::class);
    }
}
