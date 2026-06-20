<?php

namespace App\Actions\Payments;

use App\Enums\BookingStatus;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecordPaymentFailure
{
    /**
     * @throws ValidationException
     */
    public function handle(User $guest, Booking $booking, string $reason = 'demo_failure'): Booking
    {
        return DB::transaction(function () use ($guest, $booking, $reason): Booking {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ((int) $booking->guest_user_id !== (int) $guest->id) {
                throw ValidationException::withMessages([
                    'payment' => __('booking.payment_page.errors.not_your_booking'),
                ]);
            }

            if (! in_array($this->statusValue($booking), ConfirmDemoPayment::payableBookingStatuses(), true)) {
                throw ValidationException::withMessages([
                    'payment' => __('booking.payment_page.errors.not_payable'),
                ]);
            }

            $booking->paymentRecords()->create([
                'payer_user_id' => $guest->id,
                'provider' => 'demo_manual',
                'provider_reference' => 'failed-'.Str::uuid(),
                'amount' => (float) ($booking->total_amount ?: $booking->total),
                'currency' => $booking->currency ?: 'EUR',
                'status' => PaymentRecordStatus::Failed,
                'paid_at' => null,
                'metadata_json' => [
                    'driver' => 'demo_manual',
                    'reason' => $reason,
                ],
            ]);

            $status = $this->statusValue($booking);

            $booking->forceFill([
                'status' => BookingStatus::AwaitingPayment,
                'payment_status' => PaymentStatus::Failed,
            ])->save();

            $booking->statusHistories()->create([
                'from_status' => $status,
                'to_status' => BookingStatus::AwaitingPayment->value,
                'changed_by_user_id' => $guest->id,
                'note' => 'booking.payment.history.failed',
            ]);

            return $booking->refresh()->load(['paymentRecords', 'statusHistories']);
        });
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
