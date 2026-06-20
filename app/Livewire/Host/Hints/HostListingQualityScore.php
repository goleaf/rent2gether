<?php

namespace App\Livewire\Host\Hints;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\HostHints\HostListingQualityService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostListingQualityScore extends Component
{
    #[Locked]
    public string $targetType;

    #[Locked]
    public int $targetId;

    public function mount(string $targetType, int $targetId): void
    {
        $this->targetType = $targetType;
        $this->targetId = $targetId;
    }

    public function render(HostListingQualityService $quality): View
    {
        $target = $this->target();
        $readiness = $target instanceof Property || $target instanceof Room || $target instanceof SleepingPlace
            ? $quality->getPublishReadiness($target)
            : ['ready' => false, 'score' => 0, 'critical' => [], 'required' => [], 'recommended' => []];

        return view('livewire.host.hints.host-listing-quality-score', [
            'readiness' => $readiness,
        ]);
    }

    private function target(): Property|Room|SleepingPlace|null
    {
        return match ($this->targetType) {
            'property' => Property::query()->find($this->targetId),
            'room' => Room::query()->find($this->targetId),
            'sleeping_place' => SleepingPlace::query()->find($this->targetId),
            default => null,
        };
    }
}
