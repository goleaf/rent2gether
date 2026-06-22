<?php

namespace App\Models;

use Database\Factories\CleaningTaskIssueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningTaskIssue extends Model
{
    /** @use HasFactory<CleaningTaskIssueFactory> */
    use HasFactory;

    protected $fillable = [
        'cleaning_task_id',
        'booking_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'issue_type',
        'severity',
        'status',
        'description',
        'creates_maintenance_request',
        'creates_deposit_review',
        'creates_complaint',
        'blocks_calendar',
        'maintenance_request_id',
        'booking_deposit_case_id',
        'complaint_case_id',
        'resolved_at',
    ];

    protected $attributes = [
        'severity' => 'medium',
        'status' => 'reported',
        'creates_maintenance_request' => false,
        'creates_deposit_review' => false,
        'creates_complaint' => false,
        'blocks_calendar' => false,
    ];

    /**
     * Defines how stored cleaning issue attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'creates_maintenance_request' => 'boolean',
            'creates_deposit_review' => 'boolean',
            'creates_complaint' => 'boolean',
            'blocks_calendar' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this issue to the cleaning task where it was found.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    /**
     * Links this issue to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this issue to the host who owns the affected place.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this issue to the affected property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this issue to the affected room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this issue to the affected sleeping place.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this issue to a complaint case created from it.
     */
    public function complaintCase(): BelongsTo
    {
        return $this->belongsTo(ComplaintCase::class);
    }
}
