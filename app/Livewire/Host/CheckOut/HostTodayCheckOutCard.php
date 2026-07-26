<?php

namespace App\Livewire\Host\CheckOut;

use App\Models\BookingCheckOut;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostTodayCheckOutCard extends Component
{
    #[Locked]
    public ?int $checkOutId = null;

    public function mount(BookingCheckOut|int|null $checkOut = null): void
    {
        $this->checkOutId = $checkOut instanceof BookingCheckOut ? $checkOut->id : ($checkOut ? (int) $checkOut : null);
    }

    public function render(): View
    {
        return view('livewire.host.check-out.details-sheet', [
            'checkOut' => $this->checkOut,
            'variant' => 'today_card',
        ]);
    }

    #[Computed]
    public function checkOut(): ?BookingCheckOut
    {
        if ($this->checkOutId === null) {
            return null;
        }

        return BookingCheckOut::query()
            ->select([
                'id',
                'guest_user_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'check_out_date',
                'planned_check_out_time',
                'cleaning_required',
                'deposit_review_required',
            ])
            ->with([
                'guest:id,name',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
                'steps:id,booking_check_out_id,step_key,status',
            ])
            ->find($this->checkOutId);
    }
}
