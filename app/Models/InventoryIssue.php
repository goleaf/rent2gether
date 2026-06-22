<?php

namespace App\Models;

use Database\Factories\InventoryIssueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryIssue extends Model
{
    /** @use HasFactory<InventoryIssueFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_issue_number',
        'inventory_item_id',
        'inventory_item_unit_id',
        'booking_id',
        'booking_stay_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'cleaning_task_id',
        'inspection_task_id',
        'maintenance_request_id',
        'booking_deposit_case_id',
        'complaint_case_id',
        'reported_by_user_id',
        'host_user_id',
        'guest_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'issue_type',
        'severity',
        'status',
        'description',
        'quantity_affected',
        'replacement_cost_amount',
        'deduction_suggested_amount',
        'currency',
        'guest_responsibility_status',
        'booking_deposit_deduction_id',
        'maintenance_request_created_id',
        'complaint_case_created_id',
        'resolved_at',
        'closed_at',
    ];

    protected $attributes = [
        'severity' => 'medium',
        'status' => 'reported',
        'quantity_affected' => 1,
        'guest_responsibility_status' => 'unknown',
    ];

    /**
     * Defines how stored inventory issue values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'quantity_affected' => 'decimal:2',
            'replacement_cost_amount' => 'decimal:2',
            'deduction_suggested_amount' => 'decimal:2',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this issue to the affected item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Links this issue to the affected physical unit when tracked.
     */
    public function inventoryItemUnit(): BelongsTo
    {
        return $this->belongsTo(InventoryItemUnit::class);
    }

    /**
     * Links this issue to the related booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this issue to the host who owns the item.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this issue to the guest when a booking guest may be responsible.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this issue to the user or process that reported it.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    /**
     * Links this issue to its property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this issue to its room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this issue to its sleeping-place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists evidence files attached to this issue.
     */
    public function media(): HasMany
    {
        return $this->hasMany(InventoryIssueMedia::class);
    }

    /**
     * Lists timeline events recorded for this issue.
     */
    public function events(): HasMany
    {
        return $this->hasMany(InventoryEvent::class);
    }
}
