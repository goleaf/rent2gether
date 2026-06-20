<?php

namespace App\Livewire\Host\Hints;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostHints\HostHintService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostBeforePublishChecklist extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function render(HostHintService $hints): View
    {
        $host = auth()->user();
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);

        return view('livewire.host.hints.host-before-publish-checklist', [
            'hints' => $host instanceof User && $place instanceof SleepingPlace
                ? $hints->getHintsBeforePublish($host, $place)->map->toDisplayArray(app()->getLocale())->all()
                : [],
        ]);
    }
}
