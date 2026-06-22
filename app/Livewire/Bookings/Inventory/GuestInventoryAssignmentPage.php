<?php

namespace App\Livewire\Bookings\Inventory;

use App\Models\BookingInventoryAssignment;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestInventoryAssignmentPage extends Component
{
    public ?int $assignmentId = null;

    public function render(): View
    {
        return view('livewire.bookings.inventory.guest-inventory-assignment-page', [
            'assignment' => $this->assignmentId ? BookingInventoryAssignment::query()
                ->with('inventoryItem:id,name,item_type')
                ->where('guest_user_id', auth()->id())
                ->find($this->assignmentId) : null,
        ]);
    }
}
