<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CheckoutRecord;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HostConfirmCheckOut
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function handle(User $host, Booking $booking, array $data = []): CheckoutRecord
    {
        $validated = Validator::make($data, [
            'place_inspected' => ['nullable', 'boolean'],
            'no_damage' => ['nullable', 'boolean'],
            'damage_found' => ['nullable', 'boolean'],
            'damage_description' => ['nullable', 'string', 'max:2000'],
            'damage_media' => ['array', 'max:6'],
            'damage_media.*' => ['string', 'max:255'],
            'deposit_action' => ['nullable', 'in:return,hold,none'],
        ], [], app('translator')->get('booking.checkout.validation_attributes'))->validate();

        return DB::transaction(function () use ($host, $booking, $validated): CheckoutRecord {
            $booking = $this->lockBooking($booking);

            $this->ensureHostOwnsBooking($host, $booking);
            $this->ensureCanConfirm($booking);

            $now = now();
            $fromStatus = $this->statusValue($booking);
            $damageFound = (bool) ($validated['damage_found'] ?? false);
            $depositAction = (string) ($validated['deposit_action'] ?? ($damageFound ? 'hold' : 'return'));

            $record = CheckoutRecord::query()->updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'planned_time' => $this->plannedTime($booking),
                    'planned_checkout_time' => $this->plannedTime($booking),
                    'actual_departure_at' => $booking->checked_out_at ?: $now,
                    'actual_checkout_at' => $booking->checked_out_at ?: $now,
                    'host_confirmed' => true,
                    'host_confirmed_checkout_at' => $now,
                    'has_damage' => $damageFound,
                    'no_damage' => ! $damageFound,
                    'damage_found' => $damageFound,
                    'damage_description' => $validated['damage_description'] ?? null,
                    'damage_media' => $validated['damage_media'] ?? [],
                    'deposit_withheld' => $depositAction === 'hold',
                    'deposit_action' => $depositAction,
                    'withhold_reason' => $depositAction === 'hold' ? ($validated['damage_description'] ?? null) : null,
                    'status' => $damageFound ? 'problem' : 'completed',
                ],
            );

            $booking->forceFill([
                'status' => BookingStatus::Completed,
                'host_confirmed_checkout_at' => $now,
                'checked_out_at' => $booking->checked_out_at ?: $now,
                'deposit_released_at' => $depositAction === 'return' ? $now : $booking->deposit_released_at,
            ])->save();

            $this->syncDeposit($booking, $depositAction, $validated['damage_description'] ?? null, $now);
            $this->recordStatusChange($booking, $fromStatus, BookingStatus::Completed->value, $host, 'booking.checkout.history.host_confirmed');

            if ($depositAction === 'return') {
                app(NotificationService::class)->notifyGuestDepositReturned($booking);
            }

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
                'checked_out_at',
                'guest_checked_in_at',
                'guest_checked_out_at',
                'checked_in_at',
                'deposit_amount',
                'deposit',
                'service_fee',
                'service_fee_amount',
                'total',
                'total_amount',
                'currency',
                'host_reply',
                'host_response',
                'cancel_reason',
                'cancellation_reason',
                'deposit_released_at',
            ])
            ->with(['depositRecords:id,booking_id,amount,currency,status,held_at,released_at,withheld_amount,withhold_reason'])
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
                'booking' => __('booking.checkout.errors.not_host_booking'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureCanConfirm(Booking $booking): void
    {
        if (! in_array($this->statusValue($booking), [
            BookingStatus::CheckedOut->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::LeavingSoon->value,
        ], true)) {
            throw ValidationException::withMessages([
                'booking' => __('booking.checkout.errors.not_ready'),
            ]);
        }
    }

    private function syncDeposit(Booking $booking, string $depositAction, ?string $reason, mixed $now): void
    {
        $amount = (float) ($booking->deposit_amount ?: $booking->deposit);

        if ($amount <= 0) {
            return;
        }

        $record = $booking->depositRecords->sortByDesc('id')->first()
            ?: $booking->depositRecords()->create([
                'amount' => $amount,
                'currency' => $booking->currency ?: 'EUR',
                'status' => 'held',
                'held_at' => $now,
                'withheld_amount' => 0,
            ]);

        if ($depositAction === 'hold') {
            $record->update([
                'status' => 'withheld',
                'withheld_amount' => $amount,
                'withhold_reason' => $reason,
            ]);

            return;
        }

        if ($depositAction === 'return') {
            $record->update([
                'status' => 'released',
                'released_at' => $now,
                'withheld_amount' => 0,
                'withhold_reason' => null,
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
