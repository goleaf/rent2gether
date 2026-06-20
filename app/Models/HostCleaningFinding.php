<?php

namespace App\Models;

use Database\Factories\HostCleaningFindingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCleaningFinding extends Model
{
    /** @use HasFactory<HostCleaningFindingFactory> */
    use HasFactory;

    protected $fillable = [
        'host_cleaning_task_id',
        'booking_id',
        'finding_type',
        'severity',
        'description',
        'photo_paths_json',
        'needs_host_action',
        'needs_guest_notification',
        'needs_repair',
        'needs_deposit_review',
        'status',
        'resolved_at',
    ];

    protected $attributes = [
        'severity' => 'medium',
        'needs_host_action' => false,
        'needs_guest_notification' => false,
        'needs_repair' => false,
        'needs_deposit_review' => false,
        'status' => 'open',
    ];

    /**
     * Defines how Laravel converts stored Host Cleaning Finding attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'photo_paths_json' => 'array',
            'needs_host_action' => 'boolean',
            'needs_guest_notification' => 'boolean',
            'needs_repair' => 'boolean',
            'needs_deposit_review' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this Host Cleaning Finding to the Host Cleaning Task record used by its task relation.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(HostCleaningTask::class, 'host_cleaning_task_id');
    }

    /**
     * Links this Host Cleaning Finding to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
