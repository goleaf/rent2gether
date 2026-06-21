<?php

namespace App\Livewire\Bookings\Availability;

use App\Models\SleepingPlace;
use App\Services\Availability\GuestCalendarViewService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SleepingPlaceCalendar extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $from = '';

    public string $to = '';

    public ?string $selectedCheckIn = null;

    public function mount(int $sleepingPlaceId, ?string $from = null, ?string $to = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->from = $from ?? now()->toDateString();
        $this->to = $to ?? now()->addDays(14)->toDateString();
    }

    #[Computed]
    public function calendar(): array
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);

        if (! $place instanceof SleepingPlace) {
            return [
                'days' => [],
                'available_checkouts' => [],
                'nearest' => [],
            ];
        }

        return app(GuestCalendarViewService::class)->forSleepingPlace(
            $place,
            CarbonImmutable::parse($this->from),
            CarbonImmutable::parse($this->to),
            $this->selectedCheckIn ? CarbonImmutable::parse($this->selectedCheckIn) : null,
        );
    }

    public function selectCheckIn(string $date): void
    {
        $this->selectedCheckIn = $date;
    }

    public function render(): View
    {
        return view('livewire.bookings.availability.sleeping-place-calendar');
    }
}
