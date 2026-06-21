<?php

namespace App\Models;

use Database\Factories\BookingRelocationGuestResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationGuestResponse extends Model
{
    /** @use HasFactory<BookingRelocationGuestResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
        'guest_user_id',
        'response_type',
        'message',
        'selected_option_id',
        'accepted_sleeping_place_id',
        'accepted_relocation_date',
        'accepted_relocation_time',
    ];

    protected function casts(): array
    {
        return [
            'accepted_relocation_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Links this guest response to its relocation.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this guest response to the guest who made it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this guest response to the selected option when applicable.
     */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(BookingRelocationOption::class, 'selected_option_id');
    }

    /**
     * Links this guest response to the accepted sleeping place.
     */
    public function acceptedSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'accepted_sleeping_place_id');
    }
}
