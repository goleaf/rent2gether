<?php

namespace App\Livewire\Bookings\Inventory;

use App\Models\BookingInventoryAssignment;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestIssuedItemsCard extends Component
{
    public ?int $bookingId = null;

    public function render(): View
    {
        return view('livewire.bookings.inventory.guest-issued-items-card', [
            'assignments' => $this->bookingId ? BookingInventoryAssignment::query()
                ->with('inventoryItem:id,name,item_type')
                ->where('booking_id', $this->bookingId)
                ->where('guest_user_id', auth()->id())
                ->latest('id')
                ->get() : collect(),
        ]);
    }
}
