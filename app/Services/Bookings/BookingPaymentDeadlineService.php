<?php

namespace App\Services\Bookings;

use App\Models\BookingPayment;
use App\Models\BookingPaymentDeadline;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingPaymentDeadlineService
{
    public function createDeadline(BookingPayment $payment, CarbonInterface $dueAt): BookingPaymentDeadline
    {
        return BookingPaymentDeadline::query()->create([
            'booking_id' => $payment->booking_id,
            'booking_payment_id' => $payment->id,
            'deadline_type' => $payment->payment_type === 'remaining_balance' ? 'remaining_balance' : 'initial_payment',
            'due_at' => $dueAt,
            'status' => 'pending',
        ]);
    }

    public function extendDeadline(BookingPaymentDeadline $deadline, CarbonInterface $newDueAt): BookingPaymentDeadline
    {
        $deadline->forceFill([
            'due_at' => $newDueAt,
            'status' => 'extended',
        ])->save();

        return $deadline->fresh();
    }

    public function markCompleted(BookingPaymentDeadline $deadline): BookingPaymentDeadline
    {
        $deadline->forceFill(['status' => 'completed'])->save();

        return $deadline->fresh();
    }

    public function markExpired(BookingPaymentDeadline $deadline): BookingPaymentDeadline
    {
        $deadline->forceFill(['status' => 'expired'])->save();

        return $deadline->fresh();
    }

    /**
     * @return Collection<int, BookingPayment>
     */
    public function getDuePaymentsForUser(User $guest): Collection
    {
        return BookingPayment::query()
            ->where('guest_user_id', $guest->id)
            ->whereIn('status', ['waiting_payment', 'payment_started', 'pending', 'partially_paid'])
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<=', Carbon::now())
            ->orderBy('payment_deadline_at')
            ->get();
    }

    /**
     * @return Collection<int, BookingPayment>
     */
    public function getDuePaymentsForHost(User $host): Collection
    {
        return BookingPayment::query()
            ->where('host_user_id', $host->id)
            ->whereIn('status', ['waiting_payment', 'payment_started', 'pending', 'partially_paid'])
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<=', Carbon::now())
            ->orderBy('payment_deadline_at')
            ->get();
    }
}
