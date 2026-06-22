<?php

namespace App\Livewire\Host\Bookings\Inventory;

use App\Models\BookingInventoryAssignment;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostReturnInventoryChecklist extends Component
{
    public ?int $bookingId = null;

    public function render(): View
    {
        return view('livewire.host.bookings.inventory.host-return-inventory-checklist', [
            'assignments' => $this->bookingId ? BookingInventoryAssignment::query()
                ->with('inventoryItem:id,name,item_type')
                ->where('booking_id', $this->bookingId)
                ->where('host_user_id', auth()->id())
                ->where('expected_return', true)
                ->latest('id')
                ->get() : collect(),
        ]);
    }
}
