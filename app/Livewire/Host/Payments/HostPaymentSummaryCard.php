<?php

namespace App\Livewire\Host\Payments;

use App\Livewire\Bookings\Payments\Concerns\AuthorizesPaymentViewData;
use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\BookingPayment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostPaymentSummaryCard extends Component
{
    use AuthorizesPaymentViewData;
    use BuildsPaymentViewData;

    #[Locked]
    public int $paymentId;

    public function mount(int|BookingPayment $paymentId): void
    {
        $payment = $paymentId instanceof BookingPayment ? $paymentId : $this->loadPayment($paymentId);

        $this->authorizeHostPayment($payment);

        $this->paymentId = $payment->id;
    }

    public function render(): View
    {
        $payment = $this->loadPayment($this->paymentId);
        $this->authorizeHostPayment($payment);

        return view('livewire.host.payments.host-payment-summary-card', [
            'summary' => $this->paymentSummary($payment),
            'allocations' => $this->allocationRows($payment),
        ]);
    }
}
