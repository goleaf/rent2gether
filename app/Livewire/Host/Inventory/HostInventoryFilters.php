<?php

namespace App\Livewire\Host\Inventory;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryFilters extends Component
{
    public function render(): View
    {
        return view('livewire.host.inventory.host-inventory-filters', [
            'filters' => ['all', 'keys', 'bedding', 'towels', 'furniture', 'electronics', 'issued', 'lost', 'damaged', 'needs_replacement'],
        ]);
    }
}
