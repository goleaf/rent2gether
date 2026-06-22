<?php

namespace App\Models;

use Database\Factories\ReviewScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewScore extends Model
{
    /** @use HasFactory<ReviewScoreFactory> */
    use HasFactory;

    protected $fillable = [
        'review_id',
        'score_key',
        'score_value',
        'max_score',
        'weight',
        'is_public',
    ];

    /**
     * Defines how Laravel converts stored Review Score attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'score_value' => 'decimal:2',
            'max_score' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Links this Review Score to its parent review.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
