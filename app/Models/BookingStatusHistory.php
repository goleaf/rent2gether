<?php

namespace App\Models;

use Database\Factories\BookingStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusHistory extends Model
{
    /** @use HasFactory<BookingStatusHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'note',
    ];

    /**
     * Links this Booking Status History to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Status History to the User record used by its changed by relation.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
