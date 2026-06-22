<?php

namespace App\Livewire\Host\Inventory;

use App\Models\InventoryIssue;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryIssuePanel extends Component
{
    public function render(): View
    {
        return view('livewire.host.inventory.host-inventory-issue-panel', [
            'issues' => InventoryIssue::query()
                ->where('host_user_id', auth()->id())
                ->latest('id')
                ->limit(20)
                ->get(),
        ]);
    }
}
