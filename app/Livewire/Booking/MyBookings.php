<?php

namespace App\Livewire\Booking;

use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MyBookings extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'upcoming';

    #[Computed]
    public function bookings()
    {
        $query = Booking::where('guest_id', auth()->id())
            ->with(['bed.room.property'])
            ->latest();

        return match ($this->tab) {
            'upcoming' => $query->upcoming()->simplePaginate(10),
            'active' => $query->active()->simplePaginate(10),
            'past' => $query->whereIn('status', ['completed', 'checked_out', 'closed'])->simplePaginate(10),
            'cancelled' => $query->whereIn('status', ['cancelled_guest', 'cancelled_host', 'cancelled_system'])->simplePaginate(10),
            default => $query->simplePaginate(10),
        };
    }

    public function render(): View
    {
        return view('livewire.booking.my-bookings');
    }
}
