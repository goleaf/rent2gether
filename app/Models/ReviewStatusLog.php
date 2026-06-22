<?php

namespace App\Models;

use Database\Factories\ReviewStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewStatusLog extends Model
{
    /** @use HasFactory<ReviewStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored Review Status Log attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this Review Status Log to its parent review.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Links this Review Status Log to the user who changed status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
