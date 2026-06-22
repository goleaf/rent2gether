<?php

namespace App\Models;

use Database\Factories\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    /** @use HasFactory<InventoryMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'movement_number',
        'inventory_item_id',
        'inventory_item_unit_id',
        'booking_id',
        'booking_inventory_assignment_id',
        'from_location_type',
        'from_location_note',
        'to_location_type',
        'to_location_note',
        'movement_type',
        'quantity',
        'moved_by_user_id',
        'moved_at',
        'note',
    ];

    protected $attributes = [
        'quantity' => 1,
    ];

    /**
     * Defines how stored movement values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'moved_at' => 'datetime',
        ];
    }

    /**
     * Links this movement to the item that moved.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Links this movement to the physical unit that moved.
     */
    public function inventoryItemUnit(): BelongsTo
    {
        return $this->belongsTo(InventoryItemUnit::class);
    }

    /**
     * Links this movement to a booking context when guest-related.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this movement to the assignment that caused it.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(BookingInventoryAssignment::class, 'booking_inventory_assignment_id');
    }

    /**
     * Links this movement to the user who recorded it.
     */
    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by_user_id');
    }
}
