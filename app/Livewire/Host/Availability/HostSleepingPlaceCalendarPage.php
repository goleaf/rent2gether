<?php

namespace App\Livewire\Host\Availability;

use App\Models\SleepingPlace;
use App\Services\HostCalendar\HostCalendarViewService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostSleepingPlaceCalendarPage extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $from = '';

    public string $to = '';

    public function mount(int $sleepingPlaceId, ?string $from = null, ?string $to = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->from = $from ?? now()->toDateString();
        $this->to = $to ?? now()->addDays(14)->toDateString();
    }

    #[Computed]
    public function cards(): array
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);

        if (! $place instanceof SleepingPlace) {
            return [];
        }

        return app(HostCalendarViewService::class)
            ->sleepingPlaceCards($place, CarbonImmutable::parse($this->from), CarbonImmutable::parse($this->to))
            ->all();
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-sleeping-place-calendar-page');
    }
}
