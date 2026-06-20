<?php

namespace App\Livewire\Bookings;

use App\Data\Occupants\DateRange;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Compatibility\CompatibilityCalculatorService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CompatibilityCheckBeforeBooking extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public bool $confirmedWarnings = false;

    public function mount(int $sleepingPlaceId, string $checkIn = '', string $checkOut = ''): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
    }

    public function continueAnyway(CompatibilityCalculatorService $calculator): void
    {
        $result = $this->result($calculator);

        if (($result['blocking_reasons'] ?? []) !== []) {
            $this->addError('compatibility', __('compatibility.before_booking.blocking_error'));

            return;
        }

        $this->confirmedWarnings = true;
        $this->dispatch('compatibility-warnings-confirmed');
    }

    public function render(CompatibilityCalculatorService $calculator): View
    {
        return view('livewire.bookings.compatibility-check-before-booking', [
            'result' => $this->result($calculator),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function result(CompatibilityCalculatorService $calculator): ?array
    {
        $user = auth()->user();
        $place = SleepingPlace::query()->with(['room.property', 'room.compatibilityProfile', 'compatibilityProfile'])->find($this->sleepingPlaceId);

        if (! $user instanceof User || ! $place instanceof SleepingPlace || $this->checkIn === '' || $this->checkOut === '') {
            return null;
        }

        return $calculator->calculate($user, $place, new DateRange($this->checkIn, $this->checkOut))->toArray();
    }
}
