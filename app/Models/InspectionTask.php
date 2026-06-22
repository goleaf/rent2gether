<?php

namespace App\Models;

use Database\Factories\InspectionTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionTask extends Model
{
    /** @use HasFactory<InspectionTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'inspection_number',
        'booking_id',
        'booking_stay_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'cleaning_task_id',
        'booking_relocation_id',
        'complaint_case_id',
        'maintenance_request_id',
        'mismatch_report_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'inspection_type',
        'inspection_scope',
        'status',
        'priority',
        'scheduled_at',
        'actual_started_at',
        'actual_completed_at',
        'responsible_type',
        'responsible_user_id',
        'responsible_name_snapshot',
        'responsible_contact_snapshot',
        'checklist_completed',
        'photos_required',
        'photos_uploaded',
        'passed',
        'issues_found',
        'cleaning_required',
        'repair_required',
        'deposit_review_required',
        'calendar_block_required',
        'result_summary',
        'responsible_comment',
        'host_comment',
        'internal_host_note',
        'completed_at',
        'closed_at',
    ];

    protected $attributes = [
        'status' => 'scheduled',
        'priority' => 'normal',
        'responsible_type' => 'not_assigned',
        'checklist_completed' => false,
        'photos_required' => false,
        'photos_uploaded' => false,
        'passed' => false,
        'issues_found' => false,
        'cleaning_required' => false,
        'repair_required' => false,
        'deposit_review_required' => false,
        'calendar_block_required' => false,
    ];

    /**
     * Defines how stored inspection attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'actual_started_at' => 'datetime',
            'actual_completed_at' => 'datetime',
            'checklist_completed' => 'boolean',
            'photos_required' => 'boolean',
            'photos_uploaded' => 'boolean',
            'passed' => 'boolean',
            'issues_found' => 'boolean',
            'cleaning_required' => 'boolean',
            'repair_required' => 'boolean',
            'deposit_review_required' => 'boolean',
            'calendar_block_required' => 'boolean',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this inspection task to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this inspection task to the checkout that triggered it.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this inspection task to the cleaning task that preceded it.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    /**
     * Links this inspection task to the host who owns the affected place.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this inspection task to the affected property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this inspection task to the affected room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this inspection task to the affected sleeping place.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists checklist items that must be reviewed during this inspection.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InspectionTaskItem::class);
    }

    /**
     * Lists photos and media attached to this inspection.
     */
    public function media(): HasMany
    {
        return $this->hasMany(InspectionTaskMedia::class);
    }

    /**
     * Lists status transitions for this inspection.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(InspectionStatusLog::class);
    }

    /**
     * Lists timeline events recorded for this inspection.
     */
    public function events(): HasMany
    {
        return $this->hasMany(InspectionEvent::class);
    }
}
