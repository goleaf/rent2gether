<?php

namespace App\Livewire\Host\Availability;

use App\Models\SleepingPlace;
use App\Services\Availability\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostCalendarOccupancySummary extends Component
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
    public function summary(): array
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);

        if (! $place instanceof SleepingPlace) {
            return [
                'available' => 0,
                'blocked' => 0,
                'request_only' => 0,
            ];
        }

        $days = app(AvailabilityService::class)->getAvailabilityForRange($place, CarbonImmutable::parse($this->from), CarbonImmutable::parse($this->to));

        return [
            'available' => $days->where('status', 'available')->count(),
            'blocked' => $days->reject(fn (array $day): bool => in_array($day['status'], ['available', 'request_only'], true))->count(),
            'request_only' => $days->where('status', 'request_only')->count(),
        ];
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-calendar-occupancy-summary');
    }
}
