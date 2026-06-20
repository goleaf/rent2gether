<?php

namespace App\Models;

use Database\Factories\BookingReviewRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingReviewRequest extends Model
{
    /** @use HasFactory<BookingReviewRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'reviewer_user_id',
        'reviewee_user_id',
        'reviewer_role',
        'status',
        'requested_at',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_user_id');
    }
}
