<?php

namespace App\Models;

use Database\Factories\BookingRelocationHostResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationHostResponse extends Model
{
    /** @use HasFactory<BookingRelocationHostResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
        'host_user_id',
        'response_type',
        'message',
        'alternative_sleeping_place_id',
        'alternative_room_id',
        'proposed_relocation_date',
        'proposed_relocation_time',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'proposed_relocation_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Links this host response to its relocation.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this host response to the host who made it.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this host response to an offered alternative sleeping place.
     */
    public function alternativeSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'alternative_sleeping_place_id');
    }

    /**
     * Links this host response to an offered alternative room.
     */
    public function alternativeRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'alternative_room_id');
    }
}
