<?php

namespace App\Livewire\Bookings\Dates;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CheckInDatePicker extends Component
{
    public string $checkInDate = '';

    public ?string $minDate = null;

    public function mount(string $checkInDate = '', ?string $minDate = null): void
    {
        $this->checkInDate = $checkInDate;
        $this->minDate = $minDate ?: now()->toDateString();
    }

    public function render(): View
    {
        return view('livewire.bookings.dates.check-in-date-picker');
    }
}
