<?php

namespace App\Models;

use Database\Factories\InventoryEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryEvent extends Model
{
    /** @use HasFactory<InventoryEventFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'inventory_item_unit_id',
        'booking_inventory_assignment_id',
        'inventory_issue_id',
        'booking_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    protected $attributes = [
        'event_type' => 'system',
    ];

    /**
     * Defines how stored inventory event values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this event to its inventory item when item-level.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Links this event to its inventory issue when issue-level.
     */
    public function inventoryIssue(): BelongsTo
    {
        return $this->belongsTo(InventoryIssue::class);
    }
}
