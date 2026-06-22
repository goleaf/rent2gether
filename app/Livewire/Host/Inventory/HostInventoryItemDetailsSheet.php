<?php

namespace App\Livewire\Host\Inventory;

use App\Models\InventoryItem;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryItemDetailsSheet extends Component
{
    public ?int $itemId = null;

    public function render(): View
    {
        return view('livewire.host.inventory.host-inventory-item-details-sheet', [
            'item' => $this->itemId ? InventoryItem::query()
                ->with(['movements:id,inventory_item_id,movement_type,moved_at', 'issues:id,inventory_item_id,issue_type,status'])
                ->where('host_user_id', auth()->id())
                ->find($this->itemId) : null,
        ]);
    }
}
