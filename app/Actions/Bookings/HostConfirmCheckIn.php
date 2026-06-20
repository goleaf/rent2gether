<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CheckinRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HostConfirmCheckIn
{
    /**
     * @throws ValidationException
     */
    public function handle(User $host, Booking $booking): CheckinRecord
    {
        return DB::transaction(function () use ($host, $booking): CheckinRecord {
            $booking = $this->lockBooking($booking);

            $this->ensureHostOwnsBooking($host, $booking);
            $this->ensureCanConfirm($booking);

            $now = now();
            $fromStatus = $this->statusValue($booking);

            $record = CheckinRecord::query()->updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'planned_time' => $this->plannedTime($booking),
                    'actual_arrival_at' => $booking->checked_in_at ?: $now,
                    'host_confirmed' => true,
                    'host_confirmed_at' => $now,
                    'status' => 'completed',
                ],
            );

            $booking->forceFill([
                'status' => BookingStatus::InProgress,
                'host_confirmed_checkin_at' => $now,
                'checked_in_at' => $booking->checked_in_at ?: $now,
            ])->save();

            $this->recordStatusChange($booking, $fromStatus, BookingStatus::InProgress->value, $host, 'booking.checkin.history.host_confirmed');

            return $record->refresh();
        });
    }

    private function lockBooking(Booking $booking): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'guest_user_id',
                'host_user_id',
                'status',
                'guest_id',
                'host_id',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'arrival_time',
                'nights',
                'nights_count',
                'subtotal',
                'subtotal_amount',
                'cleaning_fee',
                'cleaning_fee_amount',
                'deposit',
                'deposit_amount',
                'service_fee',
                'service_fee_amount',
                'total',
                'total_amount',
                'host_reply',
                'host_response',
                'cancel_reason',
                'cancellation_reason',
                'guest_checked_in_at',
                'guest_checked_out_at',
                'checked_in_at',
                'checked_out_at',
            ])
            ->lockForUpdate()
            ->findOrFail($booking->id);
    }

    /**
     * @throws ValidationException
     */
    private function ensureHostOwnsBooking(User $host, Booking $booking): void
    {
        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw ValidationException::withMessages([
                'booking' => __('booking.checkin.errors.not_host_booking'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureCanConfirm(Booking $booking): void
    {
        if (! in_array($this->statusValue($booking), [
            BookingStatus::CheckedIn->value,
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
        ], true)) {
            throw ValidationException::withMessages([
                'booking' => __('booking.checkin.errors.not_ready'),
            ]);
        }
    }

    private function recordStatusChange(Booking $booking, string $fromStatus, string $toStatus, User $user, string $note): void
    {
        if ($fromStatus === $toStatus) {
            return;
        }

        $booking->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by_user_id' => $user->id,
            'note' => $note,
        ]);
    }

    private function plannedTime(Booking $booking): ?string
    {
        $time = $booking->arrival_time ?: $booking->check_in_time;

        return $time?->format('H:i');
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
