<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use App\Services\SleepingPlaces\SleepingPlaceCompletionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlaceCompletionPanel extends Component
{
    use HandlesSleepingPlaceStep;

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);
    }

    public function render(SleepingPlaceCompletionService $service): View
    {
        $place = $this->sleepingPlace()->loadMissing([
            'translations',
            'physicalDetails',
            'comfortDetails',
            'storageDetails',
            'positionDetails',
            'conditionDetails',
        ]);

        return view('livewire.host.sleeping-places.sleeping-place-completion-panel', [
            'completion' => $service->evaluate($place),
        ]);
    }
}
