<?php

namespace App\Models;

use Database\Factories\BookingRequestGuestResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequestGuestResponse extends Model
{
    public const TYPE_ACCEPT_PROPOSAL = 'accept_proposal';

    public const TYPE_REJECT_PROPOSAL = 'reject_proposal';

    public const TYPE_ANSWER_QUESTION = 'answer_question';

    public const TYPE_CHANGE_REQUEST = 'change_request';

    public const TYPE_WITHDRAW_REQUEST = 'withdraw_request';

    public const TYPE_SEND_MESSAGE = 'send_message';

    /** @use HasFactory<BookingRequestGuestResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_request_id',
        'guest_user_id',
        'response_type',
        'message',
        'accepted_proposed_check_in_time',
        'accepted_proposed_check_out_time',
        'accepted_proposed_check_in_date',
        'accepted_proposed_check_out_date',
        'accepted_alternative_sleeping_place_id',
    ];

    /**
     * Defines how Laravel converts stored guest response attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'accepted_proposed_check_in_date' => 'date',
            'accepted_proposed_check_out_date' => 'date',
        ];
    }

    /**
     * Links this guest response to the Booking Request.
     */
    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }

    /**
     * Links this response to the guest user who wrote it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this response to the alternative Sleeping Place accepted by the guest.
     */
    public function acceptedAlternativeSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'accepted_alternative_sleeping_place_id');
    }
}
