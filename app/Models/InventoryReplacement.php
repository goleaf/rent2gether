<?php

namespace App\Models;

use Database\Factories\InventoryReplacementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReplacement extends Model
{
    /** @use HasFactory<InventoryReplacementFactory> */
    use HasFactory;

    protected $fillable = [
        'replacement_number',
        'old_inventory_item_id',
        'old_inventory_item_unit_id',
        'new_inventory_item_id',
        'new_inventory_item_unit_id',
        'inventory_issue_id',
        'maintenance_request_id',
        'booking_deposit_deduction_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'replacement_reason',
        'status',
        'replacement_cost_amount',
        'currency',
        'purchased_at',
        'replaced_at',
        'note',
    ];

    protected $attributes = [
        'status' => 'planned',
    ];

    /**
     * Defines how stored replacement values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'replacement_cost_amount' => 'decimal:2',
            'purchased_at' => 'datetime',
            'replaced_at' => 'datetime',
        ];
    }

    /**
     * Links this replacement to the old item.
     */
    public function oldInventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'old_inventory_item_id');
    }

    /**
     * Links this replacement to the new item.
     */
    public function newInventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'new_inventory_item_id');
    }

    /**
     * Links this replacement to the issue that required it.
     */
    public function inventoryIssue(): BelongsTo
    {
        return $this->belongsTo(InventoryIssue::class);
    }

    /**
     * Links this replacement to the host who owns the item.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
