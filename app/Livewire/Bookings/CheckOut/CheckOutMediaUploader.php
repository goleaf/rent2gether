<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CheckOutMediaUploader extends Component
{
    use LoadsBookingCheckOut;
    use WithFileUploads;

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('media_uploader'));
    }
}
