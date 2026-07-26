<?php

namespace App\Livewire\Bookings\Availability;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AvailabilityWarnings extends Component
{
    /** @var list<string> */
    private array $reasons = [];

    /**
     * @param  list<string>  $reasons
     */
    public function mount(array $reasons = []): void
    {
        $this->reasons = $reasons;
    }

    public function render(): View
    {
        return view('livewire.bookings.availability.availability-warnings', [
            'reasons' => $this->reasons,
        ]);
    }
}
