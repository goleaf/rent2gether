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

    protected function casts(): array
    {
        return [
            'photo_paths_json' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    public function bookingCheckIn(): BelongsTo
    {
        return $this->checkIn();
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
}
