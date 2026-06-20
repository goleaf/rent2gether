<?php

namespace App\Services\CheckIn;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingCheckInService
{
    public function __construct(
        private readonly BookingCheckInChecklistService $checklist,
    ) {}

    public function createForBooking(Booking $booking): BookingCheckIn
    {
        $booking->loadMissing(['property:id,host_user_id', 'room:id', 'sleepingPlace:id']);

        $checkIn = BookingCheckIn::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'check_in_date' => $this->dateString($booking->check_in_date),
                'planned_check_in_time' => $this->timeString($booking->arrival_time ?: $booking->check_in_time),
                'planned_check_in_window' => $this->timeString($booking->check_in_time),
                'status' => $booking->checked_in_at ? 'checked_in' : 'instructions_available',
            ],
        );

        $this->checklist->createDefaultChecklist($checkIn);

        return $checkIn->refresh();
    }

    public function getForGuest(User $guest, Booking $booking): BookingCheckIn
    {
        $this->ensureGuestOwnsBooking($guest, $booking);

        return $this->createForBooking($booking);
    }

    public function getForHost(User $host, Booking $booking): BookingCheckIn
    {
        $this->ensureHostOwnsBooking($host, $booking);

        return $this->createForBooking($booking);
    }

    public function markGuestArrived(User $guest, BookingCheckIn $checkIn): BookingCheckIn
    {
        $this->ensureGuestOwnsCheckIn($guest, $checkIn);

        $checkIn->forceFill([
            'actual_arrival_at' => $checkIn->actual_arrival_at ?: now(),
            'status' => 'guest_arrived',
        ])->save();

        app(BookingCheckInAlertService::class)->createAlert(
            $checkIn->refresh(),
            'guest_arrived',
            'low',
            ['guest' => $checkIn->guest?->name],
        );

        return $checkIn->refresh();
    }

    public function startStayIfReady(BookingCheckIn $checkIn): BookingCheckIn
    {
        return app(BookingCheckInConfirmationService::class)->startStayIfReady($checkIn);
    }

    public function syncWithBookingStatus(BookingCheckIn $checkIn): BookingCheckIn
    {
        $checkIn->loadMissing('booking:id,status,checked_in_at');

        if ($checkIn->booking?->checked_in_at && $checkIn->status !== 'checked_in') {
            $checkIn->forceFill([
                'status' => 'checked_in',
                'actual_check_in_at' => $checkIn->actual_check_in_at ?: $checkIn->booking->checked_in_at,
            ])->save();
        }

        if ($checkIn->booking && $this->statusValue($checkIn->booking) === BookingStatus::NoShow->value) {
            $checkIn->forceFill(['status' => 'no_show'])->save();
        }

        return $checkIn->refresh();
    }

    /**
     * @return Collection<int, BookingCheckIn>
     */
    public function upcomingForUser(User $user): Collection
    {
        return BookingCheckIn::query()
            ->where(function ($query) use ($user): void {
                $query->where('guest_user_id', $user->id)
                    ->orWhere('host_user_id', $user->id);
            })
            ->whereNotIn('status', ['checked_in', 'failed', 'no_show', 'cancelled'])
            ->orderBy('check_in_date')
            ->get();
    }

    private function ensureGuestOwnsBooking(User $guest, Booking $booking): void
    {
        if ((int) $booking->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_your_booking'),
            ]);
        }
    }

    private function ensureHostOwnsBooking(User $host, Booking $booking): void
    {
        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_host_booking'),
            ]);
        }
    }

    private function ensureGuestOwnsCheckIn(User $guest, BookingCheckIn $checkIn): void
    {
        if ((int) $checkIn->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_your_booking'),
            ]);
        }
    }

    private function dateString(mixed $date): string
    {
        return $date?->format('Y-m-d') ?? now()->toDateString();
    }

    private function timeString(mixed $time): ?string
    {
        return is_object($time) && method_exists($time, 'format') ? $time->format('H:i') : $time;
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
