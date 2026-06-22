<?php

namespace App\Models;

use Database\Factories\InventoryConsumableUsageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryConsumableUsage extends Model
{
    /** @use HasFactory<InventoryConsumableUsageFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'cleaning_task_id',
        'inspection_task_id',
        'usage_type',
        'quantity_used',
        'unit',
        'used_by_user_id',
        'used_at',
        'note',
    ];

    protected $attributes = [
        'quantity_used' => 1,
        'unit' => 'pcs',
    ];

    /**
     * Defines how stored usage values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'quantity_used' => 'decimal:2',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Links this usage row to the consumed inventory item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Links this usage row to the cleaning task where it was used.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }
}
