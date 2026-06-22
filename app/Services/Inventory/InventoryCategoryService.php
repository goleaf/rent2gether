<?php

namespace App\Services\Inventory;

use App\Models\InventoryCategory;
use Illuminate\Support\Collection;

class InventoryCategoryService
{
    /**
     * @return Collection<int, InventoryCategory>
     */
    public function seedDefaultCategories(): Collection
    {
        $keys = [
            'access',
            'bedding',
            'towel',
            'furniture',
            'electronics',
            'lighting',
            'storage',
            'cleaning_supply',
            'kitchenware',
            'bathroom_item',
            'document',
            'consumable',
            'safety',
            'other',
        ];

        foreach ($keys as $index => $key) {
            InventoryCategory::query()->firstOrCreate(
                ['category_key' => $key],
                [
                    'name_translation_key' => 'inventory.categories.'.$key,
                    'description_translation_key' => 'inventory.category_descriptions.'.$key,
                    'sort_order' => $index + 1,
                    'active' => true,
                ],
            );
        }

        return $this->getActiveCategories();
    }

    /**
     * @return Collection<int, InventoryCategory>
     */
    public function getActiveCategories(): Collection
    {
        return InventoryCategory::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('category_key')
            ->get();
    }

    public function getByKey(string $key): ?InventoryCategory
    {
        return InventoryCategory::query()->where('category_key', $key)->first();
    }
}
