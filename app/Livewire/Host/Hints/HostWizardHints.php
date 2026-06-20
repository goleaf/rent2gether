<?php

namespace App\Livewire\Host\Hints;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostHints\HostHintService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostWizardHints extends Component
{
    #[Locked]
    public string $targetType;

    #[Locked]
    public int $targetId;

    public string $step = 'overview';

    public function mount(string $targetType, int $targetId, string $step = 'overview'): void
    {
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->step = $step;
    }

    public function render(HostHintService $hints): View
    {
        $host = auth()->user();
        $target = $this->target();
        $canShow = $host instanceof User && ($target instanceof Property || $target instanceof Room || $target instanceof SleepingPlace);

        return view('livewire.host.hints.host-wizard-hints', [
            'hints' => $canShow
                ? $hints->getHintsForWizard($host, $target, $this->step)->map->toDisplayArray(app()->getLocale())->all()
                : [],
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
