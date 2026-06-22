<?php

namespace App\Models;

use Database\Factories\InventoryCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    /** @use HasFactory<InventoryCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'category_key',
        'name_translation_key',
        'description_translation_key',
        'sort_order',
        'active',
    ];

    protected $attributes = [
        'sort_order' => 0,
        'active' => true,
    ];

    /**
     * Defines how stored category values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Lists inventory items grouped under this category.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
