<?php

namespace App\Livewire\Host\Inventory;

use App\Models\InventoryStockAlert;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryStockAlerts extends Component
{
    public function render(): View
    {
        return view('livewire.host.inventory.host-inventory-stock-alerts', [
            'alerts' => InventoryStockAlert::query()
                ->where('host_user_id', auth()->id())
                ->where('status', 'active')
                ->latest('id')
                ->limit(20)
                ->get(),
        ]);
    }
}
