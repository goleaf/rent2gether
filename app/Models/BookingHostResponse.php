<?php

namespace App\Models;

use Database\Factories\BookingHostResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHostResponse extends Model
{
    public const TYPE_APPROVED = 'approved';

    public const TYPE_REJECTED = 'rejected';

    public const TYPE_ASK_GUEST_QUESTION = 'ask_guest_question';

    public const TYPE_PROPOSE_TIME_CHANGE = 'propose_time_change';

    public const TYPE_OFFER_ALTERNATIVE_PLACE = 'offer_alternative_place';

    /** @use HasFactory<BookingHostResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'host_user_id',
        'response_type',
        'message',
        'proposed_check_in_time',
        'proposed_check_out_time',
        'rejection_reason',
    ];

    /**
     * Links this host response to the Booking lifecycle record.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this response to the host who made the decision.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
