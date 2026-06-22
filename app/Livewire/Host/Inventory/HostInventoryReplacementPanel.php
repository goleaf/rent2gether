<?php

namespace App\Livewire\Host\Inventory;

use App\Models\InventoryReplacement;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryReplacementPanel extends Component
{
    public function render(): View
    {
        return view('livewire.host.inventory.host-inventory-replacement-panel', [
            'replacements' => InventoryReplacement::query()
                ->where('host_user_id', auth()->id())
                ->latest('id')
                ->limit(20)
                ->get(),
        ]);
    }
}
