<?php

namespace App\Models;

use Database\Factories\InventoryCheckFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCheck extends Model
{
    /** @use HasFactory<InventoryCheckFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_check_number',
        'booking_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'cleaning_task_id',
        'inspection_task_id',
        'maintenance_request_id',
        'booking_deposit_case_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'check_type',
        'status',
        'scheduled_at',
        'completed_at',
        'checked_by_user_id',
        'checked_by_type',
        'items_expected_count',
        'items_checked_count',
        'items_missing_count',
        'items_damaged_count',
        'issues_found',
        'note',
    ];

    protected $attributes = [
        'status' => 'draft',
        'items_expected_count' => 0,
        'items_checked_count' => 0,
        'items_missing_count' => 0,
        'items_damaged_count' => 0,
        'issues_found' => false,
    ];

    /**
     * Defines how stored inventory check values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'items_expected_count' => 'integer',
            'items_checked_count' => 'integer',
            'items_missing_count' => 'integer',
            'items_damaged_count' => 'integer',
            'issues_found' => 'boolean',
        ];
    }

    /**
     * Links this check to its booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this check to the check-in flow that required it.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this check to the checkout flow that required it.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this check to a cleaning task.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    /**
     * Links this check to an inspection task.
     */
    public function inspectionTask(): BelongsTo
    {
        return $this->belongsTo(InspectionTask::class);
    }

    /**
     * Links this check to the host who owns the place.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Lists inventory items reviewed by this check.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InventoryCheckItem::class);
    }
}
