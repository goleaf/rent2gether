<?php

namespace App\Livewire\Host\Bookings;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostTodayCheckOutsPanel extends Component
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
            ->select([
                'id',
                'booking_number',
                'reference',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in_date',
                'check_out_date',
                'nights_count',
                'chargeable_days_count',
                'calendar_presence_days_count',
                'guests_count',
                'total_payable',
                'currency',
            ])
            ->where('host_user_id', $this->hostUserId ?: 0)
            ->whereDate('check_out_date', today())
            ->with(['guest:id,name', 'sleepingPlace:id,display_name,title'])
            ->limit(10)
            ->get()
            ->map(fn (Booking $booking): array => $this->bookingSummary($booking));

        return view('livewire.host.bookings.host-today-check-outs-panel', [
            'bookings' => $bookings,
        ]);
    }
}
