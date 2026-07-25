<?php

namespace App\Livewire\Host\Payments;

use App\Livewire\Bookings\Payments\Concerns\AuthorizesPaymentViewData;
use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\BookingRefund;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostRefundStatusCard extends Component
{
    use AuthorizesPaymentViewData;
    use BuildsPaymentViewData;

    #[Locked]
    public int $refundId;

    public function mount(int|BookingRefund $refundId): void
    {
        $refund = $refundId instanceof BookingRefund ? $refundId : $this->loadRefund($refundId);

        $this->authorizeHostRefund($refund);

        $this->refundId = $refund->id;
    }

    public function render(): View
    {
        $refund = $this->loadRefund($this->refundId);
        $this->authorizeHostRefund($refund);

        return view('livewire.host.payments.host-refund-status-card', [
            'summary' => $this->refundSummary($refund),
        ]);
    }
}
