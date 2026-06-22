<?php

namespace App\Models;

use Database\Factories\ReviewMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewMedia extends Model
{
    /** @use HasFactory<ReviewMediaFactory> */
    use HasFactory;

    protected $table = 'review_media';

    protected $fillable = [
        'review_id',
        'uploaded_by_user_id',
        'media_type',
        'media_role',
        'path',
        'thumbnail_path',
        'caption',
        'visibility',
        'approved_for_public_display',
        'public_display_at',
    ];

    /**
     * Defines how Laravel converts stored Review Media attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'approved_for_public_display' => 'boolean',
            'public_display_at' => 'datetime',
        ];
    }

    /**
     * Links this Review Media record to its parent review.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Links this Review Media record to the uploading user.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
