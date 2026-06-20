<?php

namespace App\Livewire\Listings\Detail;

use App\Data\Occupants\DateRange;
use App\Models\Booking;
use App\Models\Room;
use App\Services\Occupants\RoomOccupantSummaryService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CurrentOccupantsSection extends Component
{
    public int $roomId;

    public string $checkIn;

    public string $checkOut;

    public ?int $bookingId = null;

    public function mount(int $roomId, string $checkIn, string $checkOut, ?int $bookingId = null): void
    {
        $this->roomId = $roomId;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
        $this->bookingId = $bookingId;
    }

    public function render(RoomOccupantSummaryService $summaries): View
    {
        $room = Room::query()
            ->select(['id', 'property_id', 'sleeping_places_count'])
            ->findOrFail($this->roomId);
        $range = new DateRange($this->checkIn, $this->checkOut);
        $booking = $this->bookingId ? Booking::query()->find($this->bookingId) : null;
        $summary = $booking instanceof Booking
            ? $summaries->getConfirmedBookingSummary($room, $range, auth()->user(), $booking)
            : $summaries->getPreBookingSummary($room, $range);

        return view('livewire.listings.detail.current-occupants-section', [
            'summary' => $summary->toArray(),
        ]);
    }
}
