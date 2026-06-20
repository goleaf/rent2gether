<?php

namespace App\Models;

use Database\Factories\BookingCheckInChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInChecklistItem extends Model
{
    /** @use HasFactory<BookingCheckInChecklistItemFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'item_key',
        'label_key',
        'status',
        'required',
        'completed_by_user_id',
        'completed_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'completed_at' => 'datetime',
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

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
