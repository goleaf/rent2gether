<?php

namespace App\Models;

use Database\Factories\BookingCheckInProblemReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInProblemReport extends Model
{
    /** @use HasFactory<BookingCheckInProblemReportFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'problem_type',
        'severity',
        'description',
        'photo_paths_json',
        'status',
        'host_response',
        'resolved_at',
    ];

    /**
     * Defines how Laravel converts stored Booking Check In Problem Report attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'photo_paths_json' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Check In Problem Report to the Booking Check In record used by its check in relation.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this Booking Check In Problem Report to the Booking record used by its booking check in relation.
     */
    public function bookingCheckIn(): BelongsTo
    {
        return $this->checkIn();
    }

    /**
     * Links this Booking Check In Problem Report to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Check In Problem Report to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Booking Check In Problem Report to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
