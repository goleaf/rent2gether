<?php

namespace App\Livewire\Host\CheckOut;

use App\Models\BookingCheckOut;
use Illuminate\View\View;
use Livewire\Component;

class HostCheckOutDetailsSheet extends Component
{
    public ?int $checkOutId = null;

    public function mount(BookingCheckOut|int|null $checkOut = null): void
    {
        $this->checkOutId = $checkOut instanceof BookingCheckOut ? $checkOut->id : ($checkOut ? (int) $checkOut : null);
    }

    protected function checkOut(): ?BookingCheckOut
    {
        if (! $this->checkOutId) {
            return null;
        }

        return BookingCheckOut::query()
            ->with(['guest:id,name', 'room:id,title,room_number', 'sleepingPlace:id,display_name,place_number', 'steps', 'inventoryChecks', 'issues'])
            ->find($this->checkOutId);
    }

    public function render(): View
    {
        return view('livewire.host.check-out.details-sheet', [
            'checkOut' => $this->checkOut(),
            'variant' => 'details',
        ]);
    }
}
