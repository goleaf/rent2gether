<?php

namespace App\Livewire\Bookings\Payments\Concerns;

use App\Models\BookingPayment;
use App\Models\BookingRefund;
use App\Models\User;
use App\Services\Bookings\BookingPaymentPrivacyService;

trait AuthorizesPaymentViewData
{
    protected function authorizeGuestPayment(BookingPayment $payment): void
    {
        abort_unless(
            app(BookingPaymentPrivacyService::class)->canGuestViewPayment($this->currentPaymentUser(), $payment),
            403,
        );
    }

    protected function authorizeHostPayment(BookingPayment $payment): void
    {
        abort_unless(
            app(BookingPaymentPrivacyService::class)->canHostViewPayment($this->currentPaymentUser(), $payment),
            403,
        );
    }

    protected function authorizeGuestRefund(BookingRefund $refund): void
    {
        abort_unless(
            app(BookingPaymentPrivacyService::class)->canGuestViewRefund($this->currentPaymentUser(), $refund),
            403,
        );
    }

    protected function authorizeHostRefund(BookingRefund $refund): void
    {
        abort_unless(
            app(BookingPaymentPrivacyService::class)->canHostViewRefund($this->currentPaymentUser(), $refund),
            403,
        );
    }

    protected function currentPaymentUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
