<?php

namespace App\Models;

use Database\Factories\HostCleaningTaskPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCleaningTaskPhoto extends Model
{
    /** @use HasFactory<HostCleaningTaskPhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'host_cleaning_task_id',
        'uploaded_by_user_id',
        'photo_type',
        'path',
        'note',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(HostCleaningTask::class, 'host_cleaning_task_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
