<?php

namespace App\Services\CheckOut;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingStay;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\User;
use App\Services\Stays\CurrentOccupancyService;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class BookingCheckOutService
{
    public function __construct(
        private readonly BookingCheckOutChecklistService $checklist,
    ) {}

    public function createForBooking(Booking $booking): BookingCheckOut
    {
        $booking->loadMissing('stay');
        $existing = BookingCheckOut::query()->where('booking_id', $booking->id)->first();
        $checkOut = BookingCheckOut::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'checkout_number' => $existing?->checkout_number ?: app(BookingCheckOutNumberService::class)->generate(),
                'booking_stay_id' => $booking->stay?->id,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'check_out_date' => $this->dateString($booking->check_out_date),
                'planned_check_out_time' => $this->timeString($booking->check_out_time),
                'cleaning_required' => true,
                'inspection_required' => false,
                'status' => $booking->checked_out_at ? 'completed' : 'waiting_for_checkout',
            ],
        );

        $this->checklist->createDefaultChecklist($checkOut);
        app(BookingCheckOutStepService::class)->createDefaultSteps($checkOut);

        if (! $checkOut->events()->where('event_key', 'checkout_scheduled')->exists()) {
            app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'checkout_scheduled');
        }

        return $checkOut->refresh();
    }

    public function createForStay(BookingStay $stay): BookingCheckOut
    {
        $stay->loadMissing('booking');
        $checkOut = $this->createForBooking($stay->booking()->firstOrFail());

        $checkOut->forceFill([
            'booking_stay_id' => $stay->id,
            'check_out_date' => $this->dateString($stay->planned_check_out_date),
            'planned_check_out_time' => $this->timeString($stay->planned_check_out_time),
        ])->save();

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

    public function markGuestPreparing(User $guest, BookingCheckOut $checkOut): BookingCheckOut
    {
        $this->ensureGuestOwnsCheckOut($guest, $checkOut);

        $checkOut->forceFill([
            'guest_preparing_at' => $checkOut->guest_preparing_at ?: now(),
        ])->save();

        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'guest_started_checkout', [
            'user_id' => $guest->id,
        ]);

        return app(BookingCheckOutStatusService::class)->transition($checkOut->refresh(), 'guest_preparing', $guest);
    }

    public function confirmByGuest(User $guest, BookingCheckOut $checkOut): BookingCheckOut
    {
        $this->ensureGuestOwnsCheckOut($guest, $checkOut);

        $now = now();
        $checkOut->forceFill([
            'actual_check_out_at' => $checkOut->actual_check_out_at ?: $now,
            'guest_confirmed_at' => $checkOut->guest_confirmed_at ?: $now,
            'guest_confirmed_checkout_at' => $checkOut->guest_confirmed_checkout_at ?: $now,
            'host_notified_guest_checkout_at' => $checkOut->host_notified_guest_checkout_at ?: $now,
            'keys_returned' => true,
            'locker_emptied' => true,
            'locker_cleared' => true,
            'personal_items_taken' => true,
            'personal_items_removed' => true,
            'sleeping_place_free' => true,
            'sleeping_place_cleared' => true,
        ])->save();

        app(BookingCheckOutStepService::class)->markStepCompleted($checkOut->refresh(), 'guest_confirm_checkout', $guest);
        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'guest_confirmed_checkout', [
            'user_id' => $guest->id,
        ]);

        return app(BookingCheckOutStatusService::class)->transition($checkOut->refresh(), 'guest_checked_out', $guest);
    }

    public function confirmByHost(User $host, BookingCheckOut $checkOut): BookingCheckOut
    {
        $this->ensureHostOwnsCheckOut($host, $checkOut);

        $checkOut->forceFill([
            'host_confirmed_at' => $checkOut->host_confirmed_at ?: now(),
            'host_confirmed_checkout_at' => $checkOut->host_confirmed_checkout_at ?: now(),
            'room_checked' => true,
            'property_checked' => true,
            'inspection_required' => true,
        ])->save();

        app(BookingCheckOutStepService::class)->markStepCompleted($checkOut->refresh(), 'room_checked', $host);
        app(BookingCheckOutInspectionService::class)->createInspectionTask($checkOut->refresh());
        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'host_confirmed_checkout', [
            'user_id' => $host->id,
        ]);

        $updated = app(BookingCheckOutStatusService::class)->transition($checkOut->refresh(), 'waiting_inspection', $host);
        app(CurrentOccupancyService::class)->recalculateAfterCheckOut($updated->refresh());

        return $updated->refresh();
    }

    public function completeCheckout(BookingCheckOut $checkOut): BookingCheckOut
    {
        $checkOut->forceFill([
            'completed_at' => $checkOut->completed_at ?: now(),
        ])->save();

        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'checkout_completed');
        app(BookingCheckOutReviewIntegrationService::class)->createReviewRequestsAfterCheckout($checkOut->refresh());
        app(BookingCheckOutStatusService::class)->transition($checkOut->refresh(), 'completed');
        app(BookingCheckOutCalendarIntegrationService::class)->syncAvailabilityAfterCheckout($checkOut->refresh());

        return $checkOut->refresh();
    }

    public function closeCheckout(BookingCheckOut $checkOut): BookingCheckOut
    {
        $checkOut->forceFill([
            'closed_at' => $checkOut->closed_at ?: now(),
        ])->save();

        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'checkout_closed');

        return app(BookingCheckOutStatusService::class)->transition($checkOut->refresh(), 'closed');
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

    private function ensureHostOwnsCheckOut(User $host, BookingCheckOut $checkOut): void
    {
        if ((int) $checkOut->host_user_id !== (int) $host->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_out.validation.not_host_booking'),
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
