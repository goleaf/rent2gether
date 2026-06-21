<?php

namespace App\Models;

use Database\Factories\BookingExtensionGuestResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtensionGuestResponse extends Model
{
    /** @use HasFactory<BookingExtensionGuestResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_extension_id',
        'guest_user_id',
        'response_type',
        'message',
        'accepted_new_check_out_date',
        'accepted_new_check_out_time',
    ];

    protected function casts(): array
    {
        return [
            'accepted_new_check_out_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Links this guest response to the extension request.
     */
    public function bookingExtension(): BelongsTo
    {
        return $this->belongsTo(BookingExtension::class);
    }

    /**
     * Links this response to the guest who sent it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }
}
