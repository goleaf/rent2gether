<?php

namespace App\Livewire\Listings\Detail;

use App\Data\Occupants\DateRange;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Compatibility\CompatibilityCalculatorService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CompatibilityDetailsSheet extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public bool $open = false;

    public function mount(int $sleepingPlaceId, string $checkIn = '', string $checkOut = ''): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
    }

    public function show(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render(CompatibilityCalculatorService $calculator): View
    {
        $result = null;
        $user = auth()->user();
        $place = SleepingPlace::query()->with(['room.property', 'room.compatibilityProfile', 'compatibilityProfile'])->find($this->sleepingPlaceId);

        if ($user instanceof User && $place instanceof SleepingPlace && $this->checkIn !== '' && $this->checkOut !== '') {
            $result = $calculator->calculate($user, $place, new DateRange($this->checkIn, $this->checkOut))->toArray();
        }

        return view('livewire.listings.detail.compatibility-details-sheet', [
            'result' => $result,
        ]);
    }
}
