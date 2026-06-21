<?php

namespace App\Livewire\Listings;

use App\Models\Room;
use App\Services\Stays\GuestRoommatesPreviewService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CurrentRoommatesSummary extends Component
{
    public ?int $roomId = null;

    public function mount(Room|int|null $room = null): void
    {
        $this->roomId = $room instanceof Room ? $room->id : ($room ? (int) $room : null);
    }

    public function render(): View
    {
        $room = $this->roomId
            ? Room::query()->select(['id', 'property_id', 'user_id'])->find($this->roomId)
            : null;

        return view('livewire.listings.current-roommates-summary', [
            'roommates' => $room ? app(GuestRoommatesPreviewService::class)->getRoommatesForListing($room) : collect(),
        ]);
    }
}
