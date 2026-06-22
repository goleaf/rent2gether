<?php

namespace App\Livewire\Bookings\Inventory;

use App\Models\BookingInventoryAssignment;
use App\Services\Inventory\InventoryAssignmentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestConfirmItemReceivedButton extends Component
{
    public int $assignmentId;

    public function confirm(): void
    {
        $assignment = BookingInventoryAssignment::query()->findOrFail($this->assignmentId);
        app(InventoryAssignmentService::class)->guestConfirmReceived(auth()->user(), $assignment);
    }

    public function render(): View
    {
        return view('livewire.bookings.inventory.guest-confirm-item-received-button');
    }
}
