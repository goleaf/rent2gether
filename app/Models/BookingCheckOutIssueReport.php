<?php

namespace App\Models;

use Database\Factories\BookingCheckOutIssueReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckOutIssueReport extends Model
{
    /** @use HasFactory<BookingCheckOutIssueReportFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'issue_type',
        'severity',
        'description',
        'photo_paths_json',
        'status',
        'deposit_related',
        'repair_needed',
        'cleaning_needed',
        'host_response',
        'guest_response',
        'resolved_at',
    ];

    /**
     * Defines how Laravel converts stored Booking Check Out Issue Report attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'photo_paths_json' => 'array',
            'deposit_related' => 'boolean',
            'repair_needed' => 'boolean',
            'cleaning_needed' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Check Out Issue Report to the Booking Check Out record used by its check out relation.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this Booking Check Out Issue Report to the Booking record used by its booking check out relation.
     */
    public function bookingCheckOut(): BelongsTo
    {
        return $this->checkOut();
    }

    /**
     * Links this Booking Check Out Issue Report to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Check Out Issue Report to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Booking Check Out Issue Report to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
