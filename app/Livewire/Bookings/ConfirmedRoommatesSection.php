<?php

namespace App\Livewire\Bookings;

use App\Data\Occupants\DateRange;
use App\Models\Booking;
use App\Services\Occupants\RoomOccupantSummaryService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ConfirmedRoommatesSection extends Component
{
    public int $bookingId;

    public function mount(Booking|int $booking): void
    {
        $this->bookingId = $booking instanceof Booking ? $booking->id : $booking;
    }

    public function render(RoomOccupantSummaryService $summaries): View
    {
        $booking = Booking::query()
            ->with('room:id,property_id,sleeping_places_count')
            ->findOrFail($this->bookingId);

        abort_unless((int) $booking->guest_user_id === (int) auth()->id(), 403);

        return view('livewire.bookings.confirmed-roommates-section', [
            'summary' => $summaries->getConfirmedBookingSummary(
                $booking->room,
                new DateRange($booking->check_in_date, $booking->check_out_date),
                auth()->user(),
                $booking,
            )->toArray(),
        ]);
    }
}
