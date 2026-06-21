<?php

namespace App\Livewire\Host\CheckOut;

use App\Services\CheckOut\BookingCheckOutService;

class HostCheckOutConfirmButton extends HostCheckOutDetailsSheet
{
    public function confirm(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && auth()->user()) {
            app(BookingCheckOutService::class)->confirmByHost(auth()->user(), $checkOut);
        }
    }
}
