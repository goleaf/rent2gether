<?php

namespace App\Services\CheckOut;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class BookingCheckOutService
{
    public function __construct(
        private readonly BookingCheckOutChecklistService $checklist,
    ) {}

    public function createForBooking(Booking $booking): BookingCheckOut
    {
        $checkOut = BookingCheckOut::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'check_out_date' => $this->dateString($booking->check_out_date),
                'planned_check_out_time' => $this->timeString($booking->check_out_time),
                'status' => $booking->checked_out_at ? 'completed' : 'waiting_for_checkout',
            ],
        );

        $this->checklist->createDefaultChecklist($checkOut);

        return $checkOut->refresh();
    }

    public function getForGuest(User $guest, Booking $booking): BookingCheckOut
    {
        $this->ensureGuestOwnsBooking($guest, $booking);

        return $this->createForBooking($booking);
    }

    public function getForHost(User $host, Booking $booking): BookingCheckOut
    {
        $this->ensureHostOwnsBooking($host, $booking);

        return $this->createForBooking($booking);
    }

    public function markGuestReadyToLeave(User $guest, BookingCheckOut $checkOut): BookingCheckOut
    {
        $this->ensureGuestOwnsCheckOut($guest, $checkOut);

        $checkOut->forceFill(['status' => 'guest_ready_to_leave'])->save();

        return $checkOut->refresh();
    }

    public function markGuestCheckedOut(User $guest, BookingCheckOut $checkOut): BookingCheckOut
    {
        $this->ensureGuestOwnsCheckOut($guest, $checkOut);

        $checkOut->forceFill([
            'actual_check_out_at' => $checkOut->actual_check_out_at ?: now(),
            'guest_confirmed_at' => $checkOut->guest_confirmed_at ?: now(),
            'keys_returned' => true,
            'locker_emptied' => true,
            'personal_items_taken' => true,
            'status' => 'host_inspection_pending',
        ])->save();

        $this->checklist->markItemCompleted($guest, $checkOut->refresh(), 'guest_confirmed');
        app(BookingCheckOutInspectionService::class)->createInspectionTask($checkOut->refresh());
        app(BookingCheckOutCalendarService::class)->blockForCleaning($checkOut->refresh());

        return $checkOut->refresh();
    }

    public function syncWithBookingStatus(BookingCheckOut $checkOut): BookingCheckOut
    {
        $checkOut->loadMissing('booking:id,status,checked_out_at');

        if ($checkOut->booking?->checked_out_at && $checkOut->status !== 'completed') {
            $checkOut->forceFill([
                'status' => 'completed',
                'actual_check_out_at' => $checkOut->actual_check_out_at ?: $checkOut->booking->checked_out_at,
            ])->save();
        }

        if ($checkOut->booking && $this->statusValue($checkOut->booking) === BookingStatus::CancelledByGuest->value) {
            $checkOut->forceFill(['status' => 'cancelled'])->save();
        }

        return $checkOut->refresh();
    }

    public function canOfferExtension(BookingCheckOut $checkOut): bool
    {
        $date = CarbonImmutable::parse($checkOut->check_out_date)->addDay()->toDateString();

        $blockedCalendarDay = SleepingPlaceCalendarDay::query()
            ->where('sleeping_place_id', $checkOut->sleeping_place_id)
            ->whereDate('date', $date)
            ->whereIn('status', ['booked', 'blocked', 'unavailable', 'cleaning', 'repair'])
            ->exists();

        if ($blockedCalendarDay) {
            return false;
        }

        return ! Booking::query()
            ->where('sleeping_place_id', $checkOut->sleeping_place_id)
            ->where('id', '!=', $checkOut->booking_id)
            ->whereDate('check_in_date', '<=', $date)
            ->whereDate('check_out_date', '>', $date)
            ->whereNotIn('status', [
                BookingStatus::CancelledByGuest->value,
                BookingStatus::CancelledByGuestFlow->value,
                BookingStatus::CancelledByHost->value,
                BookingStatus::CancelledByHostFlow->value,
                BookingStatus::CancelledBySystem->value,
                BookingStatus::DeclinedByHost->value,
                BookingStatus::Expired->value,
            ])
            ->exists();
    }

    private function ensureGuestOwnsBooking(User $guest, Booking $booking): void
    {
        if ((int) $booking->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_out.validation.not_your_booking'),
            ]);
        }
    }

    private function ensureHostOwnsBooking(User $host, Booking $booking): void
    {
        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_out.validation.not_host_booking'),
            ]);
        }
    }

    private function ensureGuestOwnsCheckOut(User $guest, BookingCheckOut $checkOut): void
    {
        if ((int) $checkOut->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_out.validation.not_your_booking'),
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
