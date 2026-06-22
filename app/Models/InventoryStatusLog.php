<?php

namespace App\Models;

use Database\Factories\InventoryStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStatusLog extends Model
{
    /** @use HasFactory<InventoryStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'inventory_item_unit_id',
        'inventory_issue_id',
        'booking_inventory_assignment_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how stored status log values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to its inventory item when item-level.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
