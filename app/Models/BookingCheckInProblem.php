<?php

namespace App\Models;

use Database\Factories\BookingCheckInProblemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInProblem extends Model
{
    /** @use HasFactory<BookingCheckInProblemFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'problem_type',
        'severity',
        'status',
        'description',
        'guest_wants_help',
        'guest_wants_relocation',
        'guest_wants_cancellation',
        'guest_wants_refund',
        'host_response',
        'source_created_host_unresponsive_case_id',
        'source_created_complaint_case_id',
        'source_created_mismatch_report_id',
        'source_created_maintenance_request_id',
        'resolved_at',
    ];

    /**
     * Defines casts for problem flags and resolution timing.
     */
    protected function casts(): array
    {
        return [
            'guest_wants_help' => 'boolean',
            'guest_wants_relocation' => 'boolean',
            'guest_wants_cancellation' => 'boolean',
            'guest_wants_refund' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this problem to its check-in process.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this problem to its booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this problem to the guest who reported it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this problem to the host responsible for responding.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this problem to its property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this problem to its room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this problem to its sleeping-place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this problem to the host-unresponsive case created from it when present.
     */
    public function hostUnresponsiveCase(): BelongsTo
    {
        return $this->belongsTo(BookingHostUnresponsiveCase::class, 'source_created_host_unresponsive_case_id');
    }
}
