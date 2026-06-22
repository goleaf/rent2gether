<?php

namespace App\Models;

use Database\Factories\CleaningTaskMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningTaskMedia extends Model
{
    /** @use HasFactory<CleaningTaskMediaFactory> */
    use HasFactory;

    protected $table = 'cleaning_task_media';

    protected $fillable = [
        'cleaning_task_id',
        'booking_id',
        'uploaded_by_user_id',
        'media_type',
        'media_role',
        'path',
        'thumbnail_path',
        'caption',
        'visibility',
    ];

    protected $attributes = [
        'visibility' => 'host_only',
    ];

    /**
     * Links this media record to its cleaning task.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    /**
     * Links this media record to the booking context when one exists.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this media record to the uploader when one exists.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
