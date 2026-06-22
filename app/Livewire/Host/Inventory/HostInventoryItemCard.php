<?php

namespace App\Livewire\Host\Inventory;

use App\Models\InventoryItem;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryItemCard extends Component
{
    public ?int $itemId = null;

    public function render(): View
    {
        return view('livewire.host.inventory.host-inventory-item-card', [
            'item' => $this->itemId ? InventoryItem::query()->where('host_user_id', auth()->id())->find($this->itemId) : null,
        ]);
    }
}
