<?php

namespace App\Livewire\Stays;

use App\Livewire\Stays\Concerns\LoadsBookingStay;
use App\Services\Stays\StayVisibilityService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class StayVisibilitySettings extends Component
{
    use LoadsBookingStay;

    public function save(): void
    {
        $stay = $this->stay();

        if ($stay && auth()->user()) {
            app(StayVisibilityService::class)->getVisibilityPreferences($stay);
        }
    }

    public function render(): View
    {
        return view('livewire.stays.card', $this->stayViewData('visibility'));
    }
}
