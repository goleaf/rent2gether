<?php

namespace App\Livewire\Bookings\Payments;

use App\Actions\Payments\ConfirmDemoPayment;
use App\Livewire\Bookings\Payments\Concerns\AuthorizesPaymentViewData;
use App\Livewire\Bookings\Payments\Concerns\BuildsPaymentViewData;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Services\Bookings\BookingPaymentAttemptService;
use App\Services\Bookings\BookingPaymentCreationService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingPaymentPage extends Component
{
    use AuthorizesPaymentViewData;
    use BuildsPaymentViewData;

    #[Locked]
    public int $paymentId;

    public function mount(?Booking $booking = null, int|BookingPayment|null $paymentId = null): void
    {
        if ($booking instanceof Booking) {
            $this->authorizeGuestPaymentBooking($booking);

            $this->paymentId = $this->paymentForBooking($booking)->id;

            return;
        }

        if ($paymentId instanceof BookingPayment) {
            $this->authorizeGuestPayment($paymentId);

            $this->paymentId = $paymentId->id;

            return;
        }

        abort_unless(is_int($paymentId), 404);

        $payment = $this->loadPayment($paymentId);
        $this->authorizeGuestPayment($payment);

        $this->paymentId = $payment->id;
    }

    public function pay(BookingPaymentAttemptService $attempts): void
    {
        $payment = $this->loadPayment($this->paymentId);
        $this->authorizeGuestPayment($payment);

        $guest = $this->currentPaymentUser();
        $attempt = $attempts->startAttempt($guest, $payment, $payment->payment_method ?: 'internal_test');

        try {
            app(ConfirmDemoPayment::class)->handle($guest, $payment->booking);
            $attempts->markAttemptSucceeded($attempt);
        } catch (ValidationException $exception) {
            $attempts->markAttemptFailed(
                $attempt,
                collect($exception->errors())->flatten()->first() ?: 'payment_failed',
            );

            throw $exception;
        }

        session()->flash('payment-status', __('payments.messages.payment_succeeded'));
    }

    public function render(): View
    {
        $payment = $this->loadPayment($this->paymentId);
        $this->authorizeGuestPayment($payment);

        return view('livewire.bookings.payments.booking-payment-page', [
            'payment' => $payment,
            'summary' => $this->paymentSummary($payment),
            'allocations' => $this->allocationRows($payment),
            'deadlines' => $this->deadlineRows($payment),
            'receipt' => $this->receiptSummary($payment),
        ])->layout('layouts.app', [
            'title' => __('payments.title'),
        ]);
    }

    private function paymentForBooking(Booking $booking): BookingPayment
    {
        $payment = $booking->bookingPayments()
            ->latest('id')
            ->first();

        return $payment instanceof BookingPayment
            ? $payment
            : app(BookingPaymentCreationService::class)->createForBooking($booking);
    }

    private function authorizeGuestPaymentBooking(Booking $booking): void
    {
        abort_unless((int) $booking->guest_user_id === (int) $this->currentPaymentUser()->id, 403);
    }
}
