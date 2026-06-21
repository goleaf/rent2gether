<?php

namespace App\Livewire\Bookings\Availability;

use App\Models\SleepingPlace;
use App\Services\Availability\AvailabilityService;
use App\Services\Availability\SleepingPlaceCalendarStatusService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DateRangeAvailabilitySummary extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public function mount(int $sleepingPlaceId, ?string $checkIn = null, ?string $checkOut = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->checkIn = $checkIn ?? now()->toDateString();
        $this->checkOut = $checkOut ?? now()->addDay()->toDateString();
    }

    #[Computed]
    public function summary(): array
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);

        if (! $place instanceof SleepingPlace) {
            return [
                'available' => false,
                'status' => 'unavailable',
                'reasons' => ['date_unavailable'],
            ];
        }

        $availability = app(AvailabilityService::class);
        $checkIn = CarbonImmutable::parse($this->checkIn);
        $checkOut = CarbonImmutable::parse($this->checkOut);

        return [
            'available' => $availability->isAvailable($place, $checkIn, $checkOut),
            'status' => app(SleepingPlaceCalendarStatusService::class)->resolveRangeStatus($place, $checkIn, $checkOut),
            'reasons' => $availability->getBlockingReasons($place, $checkIn, $checkOut)->all(),
        ];
    }

    public function render(): View
    {
        return view('livewire.bookings.availability.date-range-availability-summary');
    }
}
