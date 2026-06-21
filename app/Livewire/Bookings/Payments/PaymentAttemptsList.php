<?php

namespace App\Livewire\Bookings\Payments;

use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\BookingPayment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PaymentAttemptsList extends Component
{
    use BuildsPaymentViewData;

    #[Locked]
    public int $paymentId;

    public function mount(int|BookingPayment $paymentId): void
    {
        $this->paymentId = $paymentId instanceof BookingPayment ? $paymentId->id : $paymentId;
    }

    public function render(): View
    {
        $payment = $this->loadPayment($this->paymentId);

        return view('livewire.bookings.payments.payment-attempts-list', [
            'attempts' => $this->attemptRows($payment),
        ]);
    }
}
