<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;

class BookingCheckOutNotificationService
{
    public function notifyGuestCheckoutSoon(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'checkout_reminder_sent');
    }

    public function notifyHostCheckoutSoon(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'checkout_reminder_sent');
    }

    public function notifyGuestCheckoutToday(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'checkout_today_notice');
    }

    public function notifyHostGuestCheckedOut(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'host_notified_guest_checked_out');
    }

    public function notifyHostInspectionRequired(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'inspection_required');
    }

    public function notifyGuestDepositReviewStarted(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'deposit_review_started');
    }

    public function notifyGuestDepositReturned(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'deposit_return_started');
    }

    public function notifyGuestDepositDeductionRequested(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'deposit_deduction_requested');
    }

    public function notifyReviewRequested(BookingCheckOut $checkOut): void
    {
        $this->record($checkOut, 'review_requested');
    }

    private function record(BookingCheckOut $checkOut, string $eventKey): void
    {
        app(BookingCheckOutEventService::class)->record($checkOut, $eventKey);
    }
}
