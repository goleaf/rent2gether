<?php

namespace App\Livewire\Bookings\Payments;

use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\BookingRefund;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RefundStatusCard extends Component
{
    use BuildsPaymentViewData;

    #[Locked]
    public int $refundId;

    public function mount(int|BookingRefund $refundId): void
    {
        $this->refundId = $refundId instanceof BookingRefund ? $refundId->id : $refundId;
    }

    public function render(): View
    {
        return view('livewire.bookings.payments.refund-status-card', [
            'summary' => $this->refundSummary($this->loadRefund($this->refundId)),
        ]);
    }
}
