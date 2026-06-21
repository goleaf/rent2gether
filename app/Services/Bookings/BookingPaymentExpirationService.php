<?php

namespace App\Services\Bookings;

use App\Models\BookingPayment;
use App\Models\User;

class BookingPaymentExpirationService
{
    public function __construct(
        private readonly BookingPaymentStateService $states,
    ) {}

    public function expirePayment(BookingPayment $payment): BookingPayment
    {
        if ($payment->status === 'paid') {
            return $payment;
        }

        $payment = $this->states->markExpired($payment);

        $payment->deadlines()->whereIn('status', ['pending', 'extended'])->update([
            'status' => 'expired',
            'updated_at' => now(),
        ]);

        return $payment->fresh();
    }

    public function expireDuePaymentsForUser(User $guest): int
    {
        return $this->expireDuePayments('guest_user_id', $guest->id);
    }

    public function expireDuePaymentsForHost(User $host): int
    {
        return $this->expireDuePayments('host_user_id', $host->id);
    }

    public function releaseExpiredPaymentLocks(): int
    {
        $count = 0;

        BookingPayment::query()
            ->whereIn('status', ['waiting_payment', 'payment_started', 'pending', 'partially_paid'])
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<=', now())
            ->each(function (BookingPayment $payment) use (&$count): void {
                $this->expirePayment($payment);
                $count++;
            });

        return $count;
    }

    private function expireDuePayments(string $column, int $userId): int
    {
        $count = 0;

        BookingPayment::query()
            ->where($column, $userId)
            ->whereIn('status', ['waiting_payment', 'payment_started', 'pending', 'partially_paid'])
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<=', now())
            ->each(function (BookingPayment $payment) use (&$count): void {
                $this->expirePayment($payment);
                $count++;
            });

        return $count;
    }
}
