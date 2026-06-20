<?php

namespace App\Livewire\Listings\Detail;

use App\Data\Occupants\DateRange;
use App\Models\Room;
use App\Models\User;
use App\Services\Occupants\RoommateCompatibilityService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoommateCompatibilitySection extends Component
{
    public int $roomId;

    public string $checkIn;

    public string $checkOut;

    public ?int $guestId = null;

    public function mount(int $roomId, string $checkIn, string $checkOut, ?int $guestId = null): void
    {
        $this->roomId = $roomId;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
        $this->guestId = $guestId ?: auth()->id();
    }

    public function render(RoommateCompatibilityService $compatibilityService): View
    {
        $room = Room::query()->select(['id', 'property_id', 'sleeping_places_count'])->findOrFail($this->roomId);
        $guest = $this->guestId ? User::query()->find($this->guestId) : null;
        $compatibility = $guest instanceof User
            ? $compatibilityService->compareGuestWithOccupants($guest, $room, new DateRange($this->checkIn, $this->checkOut))->toArray()
            : ['score' => 100, 'warnings' => [], 'messages' => []];

        return view('livewire.listings.detail.roommate-compatibility-section', [
            'compatibility' => $compatibility,
        ]);
    }
}
