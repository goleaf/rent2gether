<?php

namespace App\Livewire\Bookings\Inventory;

use App\Models\InventoryIssue;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestInventoryIssueNotice extends Component
{
    public ?int $bookingId = null;

    public function render(): View
    {
        return view('livewire.bookings.inventory.guest-inventory-issue-notice', [
            'issues' => $this->bookingId ? InventoryIssue::query()
                ->where('booking_id', $this->bookingId)
                ->where('guest_user_id', auth()->id())
                ->latest('id')
                ->get() : collect(),
        ]);
    }
}
