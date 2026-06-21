<?php

namespace App\Models;

use Database\Factories\BookingExtensionHostResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtensionHostResponse extends Model
{
    /** @use HasFactory<BookingExtensionHostResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_extension_id',
        'host_user_id',
        'response_type',
        'message',
        'proposed_new_check_out_date',
        'proposed_new_check_out_time',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'proposed_new_check_out_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Links this host response to the extension request.
     */
    public function bookingExtension(): BelongsTo
    {
        return $this->belongsTo(BookingExtension::class);
    }

    /**
     * Links this response to the host who sent it.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
