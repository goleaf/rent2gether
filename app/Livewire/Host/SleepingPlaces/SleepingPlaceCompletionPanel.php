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

    /** @var list<array{key:string,label:string,complete:bool}> */
    public array $items = [];

    public int $percentage = 0;

    public function mount(SleepingPlace $sleepingPlace, SleepingPlaceCompletionService $service): void
    {
        $this->mountSleepingPlace($sleepingPlace);
        $place = $sleepingPlace->fresh([
            'property',
            'translations',
            'physicalDetails',
            'comfortDetails',
            'storageDetails',
            'positionDetails',
            'conditionDetails',
        ]);

        $this->items = $service->items($place);
        $this->percentage = $service->percentage($place);
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-completion-panel');
    }
}
