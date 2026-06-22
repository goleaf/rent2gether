<?php

namespace App\Livewire\Host\Inventory;

use App\Models\InventoryMovement;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryMovementTimeline extends Component
{
    public ?int $itemId = null;

    public function render(): View
    {
        return view('livewire.host.inventory.host-inventory-movement-timeline', [
            'movements' => $this->itemId ? InventoryMovement::query()
                ->where('inventory_item_id', $this->itemId)
                ->latest('moved_at')
                ->limit(20)
                ->get() : collect(),
        ]);
    }
}
