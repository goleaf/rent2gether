<?php

namespace App\Livewire\Bookings\Payments;

use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\BookingPayment;
use App\Services\Bookings\BookingPaymentAttemptService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingPaymentPage extends Component
{
    use BuildsPaymentViewData;

    #[Locked]
    public int $paymentId;

    public function mount(int|BookingPayment $paymentId): void
    {
        $this->paymentId = $paymentId instanceof BookingPayment ? $paymentId->id : $paymentId;
    }

    public function pay(BookingPaymentAttemptService $attempts): void
    {
        $payment = $this->loadPayment($this->paymentId);
        $attempt = $attempts->startAttempt($payment->guest, $payment, 'internal_test');
        $attempts->markAttemptSucceeded($attempt);
    }

    public function render(): View
    {
        $payment = $this->loadPayment($this->paymentId);

        return view('livewire.bookings.payments.booking-payment-page', [
            'payment' => $payment,
            'summary' => $this->paymentSummary($payment),
            'allocations' => $this->allocationRows($payment),
            'deadlines' => $this->deadlineRows($payment),
            'receipt' => $this->receiptSummary($payment),
        ]);
    }
}
