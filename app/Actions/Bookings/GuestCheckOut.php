<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CheckoutRecord;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GuestCheckOut
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function handle(User $guest, Booking $booking, array $data): CheckoutRecord
    {
        $validated = Validator::make($data, [
            'keys_returned' => ['accepted'],
            'belongings_removed' => ['accepted'],
            'locker_emptied' => ['accepted'],
            'place_clean' => ['accepted'],
        ], [], app('translator')->get('booking.checkout.validation_attributes'))->validate();

        return DB::transaction(function () use ($guest, $booking, $validated): CheckoutRecord {
            $booking = $this->lockBooking($booking);

            $this->ensureGuestOwnsBooking($guest, $booking);
            $this->ensureCanCheckOut($booking);

            $now = now();
            $fromStatus = $this->statusValue($booking);

            $record = CheckoutRecord::query()->updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'planned_time' => $this->plannedTime($booking),
                    'planned_checkout_time' => $this->plannedTime($booking),
                    'actual_departure_at' => $booking->checked_out_at ?: $now,
                    'actual_checkout_at' => $booking->checked_out_at ?: $now,
                    'keys_returned' => true,
                    'locker_emptied' => true,
                    'belongings_collected' => true,
                    'belongings_removed' => true,
                    'place_clean' => true,
                    'guest_confirmed' => true,
                    'guest_confirmed_checkout_at' => $now,
                    'status' => 'pending_host',
                    ...$validated,
                ],
            );

            $booking->forceFill([
                'status' => BookingStatus::CheckedOut,
                'guest_checked_out_at' => $booking->guest_checked_out_at ?: $now,
                'checked_out_at' => $booking->checked_out_at ?: $now,
            ])->save();

            $this->recordStatusChange($booking, $fromStatus, BookingStatus::CheckedOut->value, $guest, 'booking.checkout.history.guest_checked_out');
            app(NotificationService::class)->notifyHostGuestCheckedOut($booking);

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
                'check_out_time',
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
                'booking' => __('booking.checkout.errors.not_your_booking'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureCanCheckOut(Booking $booking): void
    {
        if (! in_array($this->statusValue($booking), [
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::LeavingSoon->value,
        ], true)) {
            throw ValidationException::withMessages([
                'booking' => __('booking.checkout.errors.not_ready'),
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
        return $booking->check_out_time?->format('H:i');
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
