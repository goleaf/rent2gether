<?php

namespace App\Livewire\Bookings\Payments;

use App\Livewire\Bookings\Payments\Concerns\AuthorizesPaymentViewData;
use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\BookingRefund;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RefundStatusCard extends Component
{
    use AuthorizesPaymentViewData;
    use BuildsPaymentViewData;

    #[Locked]
    public int $refundId;

    public function mount(int|BookingRefund $refundId): void
    {
        $refund = $refundId instanceof BookingRefund ? $refundId : $this->loadRefund($refundId);

        $this->authorizeGuestRefund($refund);

        $this->refundId = $refund->id;
    }

    public function render(): View
    {
        $refund = $this->loadRefund($this->refundId);
        $this->authorizeGuestRefund($refund);

        return view('livewire.bookings.payments.refund-status-card', [
            'summary' => $this->refundSummary($refund),
        ]);
    }
}
