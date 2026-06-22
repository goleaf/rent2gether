<?php

namespace App\Livewire\Host\Inventory;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryItemForm extends Component
{
    public function render(): View
    {
        return view('livewire.inventory.panel', [
            'titleKey' => 'inventory.panels.item_form',
            'messageKey' => 'inventory.messages.panel_ready',
        ]);
    }
}
