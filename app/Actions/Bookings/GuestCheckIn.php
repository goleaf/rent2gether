<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\CheckinRecord;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GuestCheckIn
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function handle(User $guest, Booking $booking, array $data): CheckinRecord
    {
        $validated = Validator::make($data, [
            'property_found' => ['accepted'],
            'keys_received' => ['nullable', 'boolean'],
            'code_received' => ['nullable', 'boolean'],
            'room_seen' => ['accepted'],
            'sleeping_place_shown' => ['accepted'],
            'rules_seen' => ['accepted'],
            'everything_ok' => ['accepted'],
        ], [], app('translator')->get('booking.checkin.validation_attributes'))->validate();

        if (! ((bool) ($validated['keys_received'] ?? false) || (bool) ($validated['code_received'] ?? false))) {
            throw ValidationException::withMessages([
                'keys_received' => __('booking.checkin.errors.access_missing'),
            ]);
        }

        return DB::transaction(function () use ($guest, $booking, $validated): CheckinRecord {
            $booking = $this->lockBooking($booking);

            $this->ensureGuestOwnsBooking($guest, $booking);
            $this->ensureCanCheckIn($booking);

            $now = now();
            $fromStatus = $this->statusValue($booking);
            $keysReceived = (bool) ($validated['keys_received'] ?? false);

            $record = CheckinRecord::query()->updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'planned_time' => $this->plannedTime($booking),
                    'actual_arrival_at' => $booking->checked_in_at ?: $now,
                    'property_found' => true,
                    'keys_handed' => $keysReceived,
                    'keys_received' => $keysReceived,
                    'code_received' => (bool) ($validated['code_received'] ?? false),
                    'room_shown' => true,
                    'sleeping_place_shown' => true,
                    'rules_explained' => true,
                    'everything_ok' => true,
                    'guest_confirmed' => true,
                    'guest_confirmed_at' => $now,
                    'status' => 'completed',
                ],
            );

            $booking->forceFill([
                'status' => BookingStatus::CheckedIn,
                'guest_checked_in_at' => $booking->guest_checked_in_at ?: $now,
                'checked_in_at' => $booking->checked_in_at ?: $now,
            ])->save();

            $this->recordStatusChange($booking, $fromStatus, BookingStatus::CheckedIn->value, $guest, 'booking.checkin.history.guest_checked_in');
            app(NotificationService::class)->notifyHostGuestCheckedIn($booking);

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
                'payment_status',
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
    private function ensureGuestOwnsBooking(User $guest, Booking $booking): void
    {
        if ((int) $booking->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('booking.checkin.errors.not_your_booking'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureCanCheckIn(Booking $booking): void
    {
        if (! in_array($this->statusValue($booking), $this->checkInStatuses(), true)) {
            throw ValidationException::withMessages([
                'booking' => __('booking.checkin.errors.not_ready'),
            ]);
        }

        if ($this->paymentStatusValue($booking) !== PaymentStatus::Paid->value) {
            throw ValidationException::withMessages([
                'booking' => __('booking.checkin.errors.payment_required'),
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function checkInStatuses(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
        ];
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

    private function paymentStatusValue(Booking $booking): string
    {
        return $booking->payment_status instanceof PaymentStatus
            ? $booking->payment_status->value
            : (string) $booking->payment_status;
    }
}
