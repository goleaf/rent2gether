<?php

namespace App\Livewire\Host\Bookings\Inventory;

use App\Models\InventoryCheck;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInventoryCheckPanel extends Component
{
    public ?int $bookingId = null;

    public function render(): View
    {
        return view('livewire.host.bookings.inventory.host-inventory-check-panel', [
            'checks' => $this->bookingId ? InventoryCheck::query()
                ->where('booking_id', $this->bookingId)
                ->where('host_user_id', auth()->id())
                ->latest('id')
                ->get() : collect(),
        ]);
    }
}
