<?php

namespace App\Models;

use Database\Factories\BookingStayStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStayStatusLog extends Model
{
    /** @use HasFactory<BookingStayStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_stay_id',
        'booking_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    public function bookingStay(): BelongsTo
    {
        return $this->stay();
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
