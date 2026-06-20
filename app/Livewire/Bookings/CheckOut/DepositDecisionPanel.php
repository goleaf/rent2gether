<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use App\Services\CheckOut\BookingDepositDecisionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class DepositDecisionPanel extends Component
{
    use LoadsBookingCheckOut;

    public string $deductionAmount = '';

    public string $deductionReason = '';

    public function returnFull(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && Auth::user()) {
            app(BookingDepositDecisionService::class)->returnFullDeposit(Auth::user(), $checkOut);
            $this->refreshCheckOutState();
        }
    }

    public function requestDeduction(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && Auth::user()) {
            app(BookingDepositDecisionService::class)->requestPartialDeduction(
                Auth::user(),
                $checkOut,
                $this->deductionAmount ?: 0,
                $this->deductionReason,
            );
            $this->refreshCheckOutState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('deposit_decision'));
    }
}
