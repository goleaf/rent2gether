<?php

namespace App\Models;

use Database\Factories\InventoryItemUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItemUnit extends Model
{
    /** @use HasFactory<InventoryItemUnitFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'unit_number',
        'unit_label',
        'status',
        'condition_status',
        'current_location_type',
        'current_location_note',
        'assigned_booking_id',
        'assigned_guest_user_id',
        'serial_number',
        'barcode',
        'qr_code',
        'last_checked_at',
        'last_issued_at',
        'last_returned_at',
        'last_cleaned_at',
        'last_repaired_at',
    ];

    protected $attributes = [
        'status' => 'available',
        'condition_status' => 'good',
        'current_location_type' => 'property',
    ];

    /**
     * Defines how stored inventory unit values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
            'last_issued_at' => 'datetime',
            'last_returned_at' => 'datetime',
            'last_cleaned_at' => 'datetime',
            'last_repaired_at' => 'datetime',
        ];
    }

    /**
     * Links this physical unit to its parent inventory item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Links this physical unit to the booking it is currently assigned to.
     */
    public function assignedBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'assigned_booking_id');
    }

    /**
     * Links this physical unit to the guest currently holding it.
     */
    public function assignedGuest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_guest_user_id');
    }

    /**
     * Lists booking assignments that used this physical unit.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(BookingInventoryAssignment::class);
    }
}
