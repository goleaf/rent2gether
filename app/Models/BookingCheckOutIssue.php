<?php

namespace App\Models;

use Database\Factories\BookingCheckOutIssueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckOutIssue extends Model
{
    /** @use HasFactory<BookingCheckOutIssueFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'issue_type',
        'severity',
        'status',
        'description',
        'amount_requested',
        'currency',
        'guest_response',
        'host_response',
        'source_created_deposit_deduction_id',
        'source_created_maintenance_request_id',
        'source_created_complaint_case_id',
        'source_created_inventory_issue_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_requested' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
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
}
