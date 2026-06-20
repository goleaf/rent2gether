<?php

namespace App\Models;

use Database\Factories\UserActivitySummaryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivitySummary extends Model
{
    /** @use HasFactory<UserActivitySummaryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'completed_stays_as_guest',
        'completed_stays_as_host',
        'cancelled_by_guest_count',
        'cancelled_by_host_count',
        'no_show_count',
        'complaints_submitted_count',
        'complaints_received_count',
        'confirmed_complaints_count',
        'reviews_received_count',
        'reviews_left_count',
        'average_guest_rating',
        'average_host_rating',
        'average_response_time_minutes',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_stays_as_guest' => 'integer',
            'completed_stays_as_host' => 'integer',
            'cancelled_by_guest_count' => 'integer',
            'cancelled_by_host_count' => 'integer',
            'no_show_count' => 'integer',
            'complaints_submitted_count' => 'integer',
            'complaints_received_count' => 'integer',
            'confirmed_complaints_count' => 'integer',
            'reviews_received_count' => 'integer',
            'reviews_left_count' => 'integer',
            'average_guest_rating' => 'decimal:2',
            'average_host_rating' => 'decimal:2',
            'average_response_time_minutes' => 'integer',
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
