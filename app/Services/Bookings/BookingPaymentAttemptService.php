<?php

namespace App\Services\Bookings;

use App\Models\BookingPayment;
use App\Models\BookingPaymentAttempt;
use App\Models\BookingPaymentStatusLog;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingPaymentAttemptService
{
    public function __construct(
        private readonly BookingPaymentStateService $states,
        private readonly BookingPaymentService $payments,
    ) {}

    public function startAttempt(User $guest, BookingPayment $payment, string $method): BookingPaymentAttempt
    {
        if ((int) $payment->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'payment' => __('payments.validation.not_allowed'),
            ]);
        }

        if (! $this->payments->canRetryPayment($payment)) {
            throw ValidationException::withMessages([
                'payment' => __('payments.validation.deadline_expired'),
            ]);
        }

        $attemptNumber = (int) $payment->attempts()->max('attempt_number') + 1;
        $attempt = BookingPaymentAttempt::query()->create([
            'booking_payment_id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'guest_user_id' => $guest->id,
            'attempt_number' => max(1, $attemptNumber),
            'status' => 'started',
            'payment_method' => $method,
            'amount' => $payment->required_now_amount ?: $payment->amount,
            'currency' => $payment->currency,
            'started_at' => now(),
        ]);

        $this->states->markPaymentStarted($payment);
        $this->logAttempt($attempt, null, 'started', 'payment_attempt_started');

        return $attempt->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markAttemptSucceeded(BookingPaymentAttempt $attempt, array $data = []): BookingPaymentAttempt
    {
        $attempt->forceFill([
            'status' => 'succeeded',
            'amount' => $data['amount'] ?? $attempt->amount,
            'provider_status' => $data['provider_status'] ?? $attempt->provider_status,
            'provider_payload_json' => $data['provider_payload_json'] ?? $attempt->provider_payload_json,
            'succeeded_at' => $data['succeeded_at'] ?? now(),
        ])->save();

        $payment = $attempt->bookingPayment;
        $amount = (float) $attempt->amount;

        if ($amount < (float) $payment->amount) {
            $this->states->markPartiallyPaid($payment, ['amount' => $amount]);
        } else {
            $this->states->markPaid($payment, ['paid_at' => $attempt->succeeded_at]);
        }

        $payment->deadlines()->where('status', 'pending')->update([
            'status' => $payment->fresh()->status === 'paid' ? 'completed' : 'extended',
            'updated_at' => now(),
        ]);

        $this->logAttempt($attempt, 'started', 'succeeded', 'payment_attempt_succeeded');

        return $attempt->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markAttemptFailed(BookingPaymentAttempt $attempt, string $reason, array $data = []): BookingPaymentAttempt
    {
        $attempt->forceFill([
            'status' => 'failed',
            'provider_error_code' => $data['provider_error_code'] ?? null,
            'provider_error_message' => $data['provider_error_message'] ?? $reason,
            'provider_payload_json' => $data['provider_payload_json'] ?? $attempt->provider_payload_json,
            'failed_at' => now(),
        ])->save();

        $payment = $attempt->bookingPayment;

        if ($payment->payment_deadline_at !== null && now()->greaterThanOrEqualTo($payment->payment_deadline_at)) {
            $this->states->markFailed($payment, $reason);
        } else {
            $payment->forceFill(['status' => 'waiting_payment'])->save();
            $this->states->syncBookingPaymentStatus($payment->booking);
        }

        $this->logAttempt($attempt, 'started', 'failed', 'payment_attempt_failed', $reason);

        return $attempt->fresh();
    }

    public function markAttemptCancelled(BookingPaymentAttempt $attempt): BookingPaymentAttempt
    {
        $attempt->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ])->save();

        $this->logAttempt($attempt, null, 'cancelled', 'payment_attempt_cancelled');

        return $attempt->fresh();
    }

    public function markAttemptExpired(BookingPaymentAttempt $attempt): BookingPaymentAttempt
    {
        $attempt->forceFill([
            'status' => 'expired',
            'expired_at' => now(),
        ])->save();

        $this->states->markExpired($attempt->bookingPayment);
        $this->logAttempt($attempt, null, 'expired', 'payment_attempt_expired');

        return $attempt->fresh();
    }

    private function logAttempt(BookingPaymentAttempt $attempt, ?string $oldStatus, string $newStatus, string $eventKey, ?string $note = null): void
    {
        BookingPaymentStatusLog::query()->create([
            'booking_payment_id' => $attempt->booking_payment_id,
            'booking_payment_attempt_id' => $attempt->id,
            'booking_id' => $attempt->booking_id,
            'user_id' => $attempt->guest_user_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'event_key' => $eventKey,
            'note' => $note,
        ]);
    }
}
