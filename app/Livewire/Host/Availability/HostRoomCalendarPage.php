<?php

namespace App\Livewire\Host\Availability;

use App\Models\Room;
use App\Services\HostCalendar\HostCalendarViewService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostRoomCalendarPage extends Component
{
    #[Locked]
    public int $roomId;

    public string $from = '';

    public string $to = '';

    public function mount(int $roomId, ?string $from = null, ?string $to = null): void
    {
        $this->roomId = $roomId;
        $this->from = $from ?? now()->toDateString();
        $this->to = $to ?? now()->addDays(14)->toDateString();
    }

    #[Computed]
    public function cards(): array
    {
        $room = Room::query()
            ->with(['sleepingPlaces:id,room_id,property_id,user_id,title,display_name,status'])
            ->find($this->roomId);

        if (! $room instanceof Room) {
            return [];
        }

        $view = app(HostCalendarViewService::class);
        $from = CarbonImmutable::parse($this->from);
        $to = CarbonImmutable::parse($this->to);

        return $room->sleepingPlaces
            ->flatMap(fn ($place) => $view->sleepingPlaceCards($place, $from, $to))
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-room-calendar-page');
    }
}
