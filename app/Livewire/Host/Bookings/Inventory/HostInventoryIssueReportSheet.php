<?php

namespace App\Livewire\Host\Bookings\Inventory;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryIssueReportSheet extends Component
{
    public ?int $bookingId = null;

    public function render(): View
    {
        return view('livewire.inventory.panel', [
            'titleKey' => 'inventory.panels.issue_report',
            'messageKey' => 'inventory.messages.deposit_not_automatic',
        ]);
    }
}
