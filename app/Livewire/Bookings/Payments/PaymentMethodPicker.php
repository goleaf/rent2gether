<?php

namespace App\Livewire\Bookings\Payments;

use App\Livewire\Bookings\Payments\Concerns\AuthorizesPaymentViewData;
use App\Models\BookingPayment;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PaymentMethodPicker extends Component
{
    use AuthorizesPaymentViewData;

    #[Locked]
    public int $paymentId;

    public string $paymentMethod = 'internal_test';

    public function mount(int|BookingPayment $paymentId): void
    {
        $payment = $paymentId instanceof BookingPayment
            ? $paymentId
            : BookingPayment::query()
                ->select(['id', 'guest_user_id', 'payment_method'])
                ->findOrFail($paymentId);
        $this->authorizeGuestPayment($payment);

        $this->paymentId = $payment->id;
        $this->paymentMethod = $payment->payment_method ?: 'internal_test';
    }

    public function save(): void
    {
        $payment = BookingPayment::query()
            ->select(['id', 'guest_user_id'])
            ->findOrFail($this->paymentId);

        $this->authorizeGuestPayment($payment);

        $validated = $this->validate([
            'paymentMethod' => ['required', 'string', Rule::in($this->availablePaymentMethods())],
        ]);

        BookingPayment::query()->whereKey($this->paymentId)->update([
            'payment_method' => $validated['paymentMethod'],
            'updated_at' => now(),
        ]);
    }

    public function render(): View
    {
        $payment = BookingPayment::query()
            ->select(['id', 'guest_user_id'])
            ->findOrFail($this->paymentId);

        $this->authorizeGuestPayment($payment);

        return view('livewire.bookings.payments.payment-method-picker', [
            'methods' => $this->availablePaymentMethods(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function availablePaymentMethods(): array
    {
        return ['internal_test', 'manual_confirmation_future'];
    }
}
