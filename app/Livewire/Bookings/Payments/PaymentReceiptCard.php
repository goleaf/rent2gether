<?php

namespace App\Livewire\Bookings\Payments;

use App\Livewire\Bookings\Payments\Concerns\AuthorizesPaymentViewData;
use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\BookingPayment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PaymentReceiptCard extends Component
{
    use AuthorizesPaymentViewData;
    use BuildsPaymentViewData;

    #[Locked]
    public int $paymentId;

    public function mount(int|BookingPayment $paymentId): void
    {
        $payment = $paymentId instanceof BookingPayment ? $paymentId : $this->loadPayment($paymentId);

        $this->authorizeGuestPayment($payment);

        $this->paymentId = $payment->id;
    }

    public function render(): View
    {
        $payment = $this->loadPayment($this->paymentId);
        $this->authorizeGuestPayment($payment);

        return view('livewire.bookings.payments.payment-receipt-card', [
            'receipt' => $this->receiptSummary($payment),
        ]);
    }
}
