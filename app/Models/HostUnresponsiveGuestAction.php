<?php

namespace App\Models;

use Database\Factories\HostUnresponsiveGuestActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostUnresponsiveGuestAction extends Model
{
    /** @use HasFactory<HostUnresponsiveGuestActionFactory> */
    use HasFactory;

    protected $fillable = [
        'host_unresponsive_case_id',
        'booking_id',
        'guest_user_id',
        'action_type',
        'message',
        'guest_location_note',
        'new_waiting_until',
    ];

    protected function casts(): array
    {
        return [
            'new_waiting_until' => 'datetime',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(BookingHostUnresponsiveCase::class, 'host_unresponsive_case_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }
}
