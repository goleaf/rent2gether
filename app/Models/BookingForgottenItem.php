<?php

namespace App\Models;

use Database\Factories\BookingForgottenItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingForgottenItem extends Model
{
    /** @use HasFactory<BookingForgottenItemFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'item_name',
        'description',
        'photo_paths_json',
        'storage_location',
        'status',
        'guest_notified_at',
        'picked_up_at',
        'disposed_at',
        'keep_until',
    ];

    protected function casts(): array
    {
        return [
            'photo_paths_json' => 'array',
            'guest_notified_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'disposed_at' => 'datetime',
            'keep_until' => 'date:Y-m-d',
        ];
    }

    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    public function bookingCheckOut(): BelongsTo
    {
        return $this->checkOut();
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
