<?php

namespace App\Models;

use Database\Factories\BookingRequestHostResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequestHostResponse extends Model
{
    public const TYPE_APPROVE = 'approve';

    public const TYPE_REJECT = 'reject';

    public const TYPE_ASK_QUESTION = 'ask_question';

    public const TYPE_PROPOSE_TIME_CHANGE = 'propose_time_change';

    public const TYPE_PROPOSE_DATE_CHANGE = 'propose_date_change';

    public const TYPE_OFFER_ALTERNATIVE_SLEEPING_PLACE = 'offer_alternative_sleeping_place';

    public const TYPE_OFFER_ALTERNATIVE_ROOM = 'offer_alternative_room';

    /** @use HasFactory<BookingRequestHostResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_request_id',
        'host_user_id',
        'response_type',
        'message',
        'proposed_check_in_time',
        'proposed_check_out_time',
        'proposed_check_in_date',
        'proposed_check_out_date',
        'alternative_sleeping_place_id',
        'alternative_room_id',
        'rejection_reason',
    ];

    /**
     * Defines how Laravel converts stored host response attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'proposed_check_in_date' => 'date',
            'proposed_check_out_date' => 'date',
        ];
    }

    /**
     * Links this host response to the Booking Request.
     */
    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }

    /**
     * Links this response to the host user who wrote it.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this response to the alternative Sleeping Place when offered.
     */
    public function alternativeSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'alternative_sleeping_place_id');
    }

    /**
     * Links this response to the alternative Room when offered.
     */
    public function alternativeRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'alternative_room_id');
    }
}
