<?php

namespace App\Models;

use Database\Factories\InspectionTaskMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionTaskMedia extends Model
{
    /** @use HasFactory<InspectionTaskMediaFactory> */
    use HasFactory;

    protected $table = 'inspection_task_media';

    protected $fillable = [
        'inspection_task_id',
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
     * Links this media record to its inspection task.
     */
    public function inspectionTask(): BelongsTo
    {
        return $this->belongsTo(InspectionTask::class);
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
