<?php

namespace App\Models;

use Database\Factories\ReviewRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReviewRequest extends Model
{
    /** @use HasFactory<ReviewRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'review_request_number',
        'booking_id',
        'booking_stay_id',
        'booking_check_out_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'request_type',
        'status',
        'reviewer_user_id',
        'reviewer_type',
        'review_subject_type',
        'review_subject_user_id',
        'due_at',
        'opened_at',
        'started_at',
        'submitted_at',
        'expired_at',
        'cancelled_at',
        'closed_at',
        'notification_sent_at',
        'reminder_sent_at',
    ];

    /**
     * Defines how Laravel converts stored Review Request attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'opened_at' => 'datetime',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'expired_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'closed_at' => 'datetime',
            'notification_sent_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * Links this Review Request to the booking that produced it.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Review Request to its checkout context.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this Review Request to the guest in the booking.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Review Request to the host in the booking.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Review Request to the user expected to submit the review.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /**
     * Links this Review Request to the property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Review Request to the room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Review Request to the sleeping place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Fetches the review submitted for this request.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
