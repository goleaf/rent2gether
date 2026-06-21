<?php

namespace App\Livewire\Host\Payments;

use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\BookingRefund;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostRefundStatusCard extends Component
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
        return view('livewire.host.payments.host-refund-status-card', [
            'summary' => $this->refundSummary($this->loadRefund($this->refundId)),
        ]);
    }
}
