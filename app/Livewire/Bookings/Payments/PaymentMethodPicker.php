<?php

namespace App\Livewire\Bookings\Payments;

use App\Models\BookingPayment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PaymentMethodPicker extends Component
{
    #[Locked]
    public int $paymentId;

    public string $paymentMethod = 'internal_test';

    public function mount(int|BookingPayment $paymentId): void
    {
        $payment = $paymentId instanceof BookingPayment ? $paymentId : BookingPayment::query()->findOrFail($paymentId);
        $this->paymentId = $payment->id;
        $this->paymentMethod = $payment->payment_method;
    }

    public function save(): void
    {
        BookingPayment::query()->whereKey($this->paymentId)->update([
            'payment_method' => $this->paymentMethod,
            'updated_at' => now(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.bookings.payments.payment-method-picker', [
            'methods' => ['internal_test', 'manual_confirmation_future'],
        ]);
    }
}
