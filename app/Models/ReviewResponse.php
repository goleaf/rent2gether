<?php

namespace App\Models;

use Database\Factories\ReviewResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewResponse extends Model
{
    /** @use HasFactory<ReviewResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'review_id',
        'responder_user_id',
        'responder_type',
        'status',
        'response_text',
        'is_public',
        'submitted_at',
        'published_at',
        'hidden_at',
    ];

    /**
     * Defines how Laravel converts stored Review Response attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
            'hidden_at' => 'datetime',
        ];
    }

    /**
     * Links this Review Response to its parent review.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Links this Review Response to the responding user.
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responder_user_id');
    }
}
