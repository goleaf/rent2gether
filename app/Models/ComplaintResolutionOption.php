<?php

namespace App\Models;

use Database\Factories\ComplaintResolutionOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintResolutionOption extends Model
{
    /** @use HasFactory<ComplaintResolutionOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_case_id',
        'resolution_type',
        'status',
        'description',
        'amount',
        'currency',
        'booking_refund_id',
        'booking_relocation_id',
        'booking_cancellation_id',
        'deposit_case_id',
        'maintenance_request_id',
        'cleaning_task_id',
        'offered_by_user_id',
        'accepted_by_user_id',
        'offered_at',
        'accepted_at',
        'rejected_at',
        'completed_at',
    ];

    protected $attributes = [
        'status' => 'offered',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'offered_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function complaintCase(): BelongsTo
    {
        return $this->belongsTo(ComplaintCase::class);
    }

    public function offeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'offered_by_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
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
}
