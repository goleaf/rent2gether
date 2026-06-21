<?php

namespace App\Livewire\Bookings\Dates;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CheckOutDatePicker extends Component
{
    public string $checkOutDate = '';

    public ?string $minDate = null;

    public function mount(string $checkOutDate = '', ?string $minDate = null): void
    {
        $this->checkOutDate = $checkOutDate;
        $this->minDate = $minDate ?: now()->addDay()->toDateString();
    }

    public function render(): View
    {
        return view('livewire.bookings.dates.check-out-date-picker');
    }
}
