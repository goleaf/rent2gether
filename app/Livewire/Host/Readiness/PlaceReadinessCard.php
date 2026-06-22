<?php

namespace App\Livewire\Host\Readiness;

use App\Models\PlaceReadinessCheck;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PlaceReadinessCard extends Component
{
    public ?int $readinessCheckId = null;

    public function render(): View
    {
        return view('livewire.host.readiness.place-readiness-card', [
            'check' => $this->check(),
        ]);
    }

    private function check(): ?PlaceReadinessCheck
    {
        return $this->readinessCheckId
            ? PlaceReadinessCheck::query()->with(['room:id,title', 'sleepingPlace:id,display_name,place_number'])->find($this->readinessCheckId)
            : null;
    }
}
