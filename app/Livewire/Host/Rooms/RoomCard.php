<?php

namespace App\Livewire\Host\Rooms;

use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RoomCard extends Component
{
    public ?int $roomId = null;

    public function mount(?int $roomId = null): void
    {
        $this->roomId = $roomId;
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function card(): ?array
    {
        if (! $this->roomId) {
            return null;
        }

        $room = Room::query()
            ->select(['id', 'title', 'type', 'room_type', 'gender_type', 'gender_policy', 'status', 'publication_status', 'free_sleeping_places_count'])
            ->withCount('sleepingPlaces')
            ->find($this->roomId);

        if (! $room) {
            return null;
        }

        return [
            'title' => $room->title,
            'type' => $room->room_type?->label() ?? $room->type?->label() ?? $room->room_type,
            'gender_policy' => $room->gender_policy?->label() ?? $room->gender_policy,
            'status' => $room->publication_status ?: ($room->status?->value ?? $room->status),
            'sleeping_places_count' => $room->sleeping_places_count,
            'free_sleeping_places_count' => $room->free_sleeping_places_count,
        ];
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-card');
    }
}
