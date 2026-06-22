<?php

namespace App\Models;

use Database\Factories\InventoryCheckItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCheckItem extends Model
{
    /** @use HasFactory<InventoryCheckItemFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_check_id',
        'inventory_item_id',
        'inventory_item_unit_id',
        'expected_present',
        'is_present',
        'expected_return',
        'is_returned',
        'expected_condition_status',
        'actual_condition_status',
        'missing',
        'damaged',
        'dirty',
        'needs_cleaning',
        'needs_washing',
        'needs_repair',
        'needs_replacement',
        'note',
    ];

    protected $attributes = [
        'expected_present' => true,
        'is_present' => false,
        'expected_return' => false,
        'is_returned' => false,
        'missing' => false,
        'damaged' => false,
        'dirty' => false,
        'needs_cleaning' => false,
        'needs_washing' => false,
        'needs_repair' => false,
        'needs_replacement' => false,
    ];

    /**
     * Defines how stored inventory check item values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'expected_present' => 'boolean',
            'is_present' => 'boolean',
            'expected_return' => 'boolean',
            'is_returned' => 'boolean',
            'missing' => 'boolean',
            'damaged' => 'boolean',
            'dirty' => 'boolean',
            'needs_cleaning' => 'boolean',
            'needs_washing' => 'boolean',
            'needs_repair' => 'boolean',
            'needs_replacement' => 'boolean',
        ];
    }

    /**
     * Links this row to the inventory check it belongs to.
     */
    public function inventoryCheck(): BelongsTo
    {
        return $this->belongsTo(InventoryCheck::class);
    }

    /**
     * Links this check row to the expected inventory item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Links this check row to the expected physical unit when tracked.
     */
    public function inventoryItemUnit(): BelongsTo
    {
        return $this->belongsTo(InventoryItemUnit::class);
    }
}
