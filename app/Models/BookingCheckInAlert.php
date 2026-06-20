<?php

namespace App\Models;

use Database\Factories\BookingCheckInAlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInAlert extends Model
{
    /** @use HasFactory<BookingCheckInAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'alert_type',
        'severity',
        'status',
        'message_key',
        'message_params_json',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
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
