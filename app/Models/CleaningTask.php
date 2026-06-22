<?php

namespace App\Models;

use Database\Factories\CleaningTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CleaningTask extends Model
{
    /** @use HasFactory<CleaningTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'cleaning_number',
        'booking_id',
        'booking_stay_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'booking_relocation_id',
        'complaint_case_id',
        'maintenance_request_id',
        'mismatch_report_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'cleaning_type',
        'cleaning_scope',
        'status',
        'priority',
        'scheduled_date',
        'scheduled_start_at',
        'scheduled_end_at',
        'actual_started_at',
        'actual_completed_at',
        'responsible_type',
        'responsible_user_id',
        'responsible_name_snapshot',
        'responsible_contact_snapshot',
        'access_required',
        'access_confirmed',
        'access_instruction_snapshot',
        'supplies_required',
        'supplies_note',
        'checklist_completed',
        'before_photos_required',
        'after_photos_required',
        'before_photos_uploaded',
        'after_photos_uploaded',
        'issues_found',
        'damage_found',
        'extra_dirt_found',
        'forgotten_items_found',
        'inventory_issue_found',
        'repair_required',
        'inspection_required',
        'deposit_review_required',
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
        'access_required' => false,
        'access_confirmed' => false,
        'supplies_required' => false,
        'checklist_completed' => false,
        'before_photos_required' => false,
        'after_photos_required' => true,
        'before_photos_uploaded' => false,
        'after_photos_uploaded' => false,
        'issues_found' => false,
        'damage_found' => false,
        'extra_dirt_found' => false,
        'forgotten_items_found' => false,
        'inventory_issue_found' => false,
        'repair_required' => false,
        'inspection_required' => false,
        'deposit_review_required' => false,
    ];

    /**
     * Defines how stored cleaning task attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'actual_started_at' => 'datetime',
            'actual_completed_at' => 'datetime',
            'access_required' => 'boolean',
            'access_confirmed' => 'boolean',
            'supplies_required' => 'boolean',
            'checklist_completed' => 'boolean',
            'before_photos_required' => 'boolean',
            'after_photos_required' => 'boolean',
            'before_photos_uploaded' => 'boolean',
            'after_photos_uploaded' => 'boolean',
            'issues_found' => 'boolean',
            'damage_found' => 'boolean',
            'extra_dirt_found' => 'boolean',
            'forgotten_items_found' => 'boolean',
            'inventory_issue_found' => 'boolean',
            'repair_required' => 'boolean',
            'inspection_required' => 'boolean',
            'deposit_review_required' => 'boolean',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this cleaning task to its booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this cleaning task to the related stay when it exists.
     */
    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    /**
     * Links this cleaning task to the related check-in when it exists.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this cleaning task to the checkout that triggered it.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this cleaning task to the relocation that triggered it.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this cleaning task to the complaint that requested it.
     */
    public function complaintCase(): BelongsTo
    {
        return $this->belongsTo(ComplaintCase::class);
    }

    /**
     * Links this cleaning task to the listing mismatch that requested it.
     */
    public function mismatchReport(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'mismatch_report_id');
    }

    /**
     * Links this cleaning task to the host who owns the affected place.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this cleaning task to the affected property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this cleaning task to the affected room when the task is narrower than a property.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this cleaning task to the affected sleeping place when the task is bed-level.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this cleaning task to the assigned responsible user when one exists.
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Lists checklist items that must be completed for this cleaning task.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CleaningTaskItem::class);
    }

    /**
     * Lists photos and media attached to this cleaning task.
     */
    public function media(): HasMany
    {
        return $this->hasMany(CleaningTaskMedia::class);
    }

    /**
     * Lists problems found while this cleaning task was being completed.
     */
    public function issues(): HasMany
    {
        return $this->hasMany(CleaningTaskIssue::class);
    }

    /**
     * Lists inspections created from this cleaning task.
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(InspectionTask::class);
    }

    /**
     * Lists inventory checks performed during this cleaning task.
     */
    public function inventoryChecks(): HasMany
    {
        return $this->hasMany(InventoryCheck::class);
    }

    /**
     * Lists status transitions for this cleaning task.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(CleaningStatusLog::class);
    }

    /**
     * Lists timeline events recorded for this cleaning task.
     */
    public function events(): HasMany
    {
        return $this->hasMany(CleaningEvent::class);
    }
}
