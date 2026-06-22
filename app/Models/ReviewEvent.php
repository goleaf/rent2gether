<?php

namespace App\Models;

use Database\Factories\ReviewEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewEvent extends Model
{
    /** @use HasFactory<ReviewEventFactory> */
    use HasFactory;

    protected $fillable = [
        'review_id',
        'review_request_id',
        'booking_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored Review Event attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this Review Event to its review when available.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Links this Review Event to its review request when available.
     */
    public function reviewRequest(): BelongsTo
    {
        return $this->belongsTo(ReviewRequest::class);
    }

    /**
     * Links this Review Event to its booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
