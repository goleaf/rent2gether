<?php

namespace App\Livewire\Host\Inventory;

use App\Models\InventoryItem;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryPage extends Component
{
    public ?string $status = null;

    public ?string $category = null;

    public function render(): View
    {
        $host = auth()->user();

        return view('livewire.host.inventory.host-inventory-page', [
            'items' => $host ? InventoryItem::query()
                ->select([
                    'id',
                    'inventory_number',
                    'host_user_id',
                    'property_id',
                    'room_id',
                    'sleeping_place_id',
                    'inventory_category_id',
                    'item_type',
                    'inventory_scope',
                    'name',
                    'status',
                    'condition_status',
                    'quantity',
                    'unit',
                    'is_returnable',
                    'is_required_for_readiness',
                    'is_promised_in_listing',
                    'current_location_type',
                    'created_at',
                ])
                ->with([
                    'category:id,category_key',
                    'room:id,title,room_number',
                    'sleepingPlace:id,display_name,place_number',
                ])
                ->where('host_user_id', $host->id)
                ->when($this->status, fn ($query) => $query->where('status', $this->status))
                ->when($this->category, fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('category_key', $this->category)))
                ->latest('id')
                ->limit(30)
                ->get() : collect(),
            'filters' => ['all', 'keys', 'bedding', 'towels', 'furniture', 'electronics', 'issued', 'lost', 'damaged', 'needs_replacement'],
        ]);
    }
}
