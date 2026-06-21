<?php

namespace App\Livewire\Host\Bookings;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostTodayCheckInsPanel extends Component
{
    use BuildsBookingViewData;

    #[Locked]
    public ?int $hostUserId = null;

    public function mount(?int $hostUserId = null): void
    {
        $this->hostUserId = $hostUserId ?: auth()->id();
    }

    public function render(): View
    {
        $bookings = Booking::query()
            ->where('host_user_id', $this->hostUserId ?: 0)
            ->whereDate('check_in_date', today())
            ->with(['guest:id,name', 'sleepingPlace:id,display_name,title'])
            ->limit(10)
            ->get()
            ->map(fn (Booking $booking): array => $this->bookingSummary($booking));

        return view('livewire.host.bookings.host-today-check-ins-panel', [
            'bookings' => $bookings,
        ]);
    }
}
