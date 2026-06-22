<?php

namespace App\Models;

use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_number',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'inventory_category_id',
        'item_type',
        'inventory_scope',
        'name',
        'description',
        'status',
        'condition_status',
        'quantity',
        'unit',
        'is_returnable',
        'is_consumable',
        'is_fixed_asset',
        'is_guest_visible',
        'is_required_for_readiness',
        'is_promised_in_listing',
        'current_location_type',
        'current_location_note',
        'storage_location',
        'serial_number',
        'barcode',
        'qr_code',
        'purchase_date',
        'purchase_price_amount',
        'currency',
        'estimated_replacement_cost_amount',
        'deposit_deduction_default_amount',
        'photo_path',
        'last_checked_at',
        'last_cleaned_at',
        'last_repaired_at',
        'last_issued_at',
        'last_returned_at',
        'retired_at',
        'disposed_at',
        'host_note',
        'internal_note',
    ];

    protected $attributes = [
        'status' => 'active',
        'condition_status' => 'good',
        'quantity' => 1,
        'unit' => 'pcs',
        'is_returnable' => false,
        'is_consumable' => false,
        'is_fixed_asset' => false,
        'is_guest_visible' => false,
        'is_required_for_readiness' => false,
        'is_promised_in_listing' => false,
        'current_location_type' => 'property',
    ];

    /**
     * Defines how stored inventory item values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'is_returnable' => 'boolean',
            'is_consumable' => 'boolean',
            'is_fixed_asset' => 'boolean',
            'is_guest_visible' => 'boolean',
            'is_required_for_readiness' => 'boolean',
            'is_promised_in_listing' => 'boolean',
            'purchase_date' => 'date:Y-m-d',
            'purchase_price_amount' => 'decimal:2',
            'estimated_replacement_cost_amount' => 'decimal:2',
            'deposit_deduction_default_amount' => 'decimal:2',
            'last_checked_at' => 'datetime',
            'last_cleaned_at' => 'datetime',
            'last_repaired_at' => 'datetime',
            'last_issued_at' => 'datetime',
            'last_returned_at' => 'datetime',
            'retired_at' => 'datetime',
            'disposed_at' => 'datetime',
        ];
    }

    /**
     * Links this inventory item to the host who owns it.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this inventory item to its property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this inventory item to its room context when it is room-level.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this inventory item to its sleeping-place context when it is place-level.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this inventory item to its translated category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    /**
     * Lists physical units tracked under this inventory item.
     */
    public function units(): HasMany
    {
        return $this->hasMany(InventoryItemUnit::class);
    }

    /**
     * Lists guest booking assignments for this item.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(BookingInventoryAssignment::class);
    }

    /**
     * Lists movement history entries for this item.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Lists issues reported for this item.
     */
    public function issues(): HasMany
    {
        return $this->hasMany(InventoryIssue::class);
    }

    /**
     * Lists stock alerts created for this item.
     */
    public function stockAlerts(): HasMany
    {
        return $this->hasMany(InventoryStockAlert::class);
    }

    /**
     * Lists status logs that affected this item.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(InventoryStatusLog::class);
    }

    /**
     * Lists timeline events recorded for this item.
     */
    public function events(): HasMany
    {
        return $this->hasMany(InventoryEvent::class);
    }
}
