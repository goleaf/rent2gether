<?php

namespace App\Livewire\Host\Inventory;

use App\Models\InventoryItemUnit;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryUnitList extends Component
{
    public ?int $itemId = null;

    public function render(): View
    {
        return view('livewire.host.inventory.host-inventory-unit-list', [
            'units' => $this->itemId ? InventoryItemUnit::query()
                ->where('inventory_item_id', $this->itemId)
                ->orderBy('id')
                ->get() : collect(),
        ]);
    }
}
