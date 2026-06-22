<?php

namespace App\Models;

use Database\Factories\InventoryStockAlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockAlert extends Model
{
    /** @use HasFactory<InventoryStockAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'alert_type',
        'status',
        'threshold_quantity',
        'current_quantity',
        'message_key',
        'resolved_at',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Defines how stored stock alert values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'threshold_quantity' => 'decimal:2',
            'current_quantity' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this alert to the item whose stock needs attention.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
