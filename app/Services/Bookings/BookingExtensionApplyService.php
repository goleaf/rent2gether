<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingStay;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\RoomCurrentOccupancySnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingExtensionApplyService
{
    public function __construct(
        private readonly BookingExtensionAvailabilityService $availability,
        private readonly BookingExtensionHoldService $holds,
        private readonly BookingExtensionEventService $events,
        private readonly BookingExtensionNotificationService $notifications,
    ) {}

    public function apply(BookingExtension $extension): Booking
    {
        return DB::transaction(function () use ($extension): Booking {
            $extension->refresh()->loadMissing('booking', 'bookingStay');

            if (! $this->canApply($extension)) {
                throw ValidationException::withMessages([
                    'extension' => __('booking_extensions.validation.payment_required'),
                ]);
            }

            $availability = $this->availability->checkAvailabilityAfterCurrentCheckout(
                $extension->booking()->firstOrFail(),
                $extension->new_check_out_date,
                $extension,
            );

            if (! $availability['available']) {
                $extension->forceFill(['status' => 'dates_unavailable'])->save();

                throw ValidationException::withMessages([
                    'extension' => __('booking_extensions.validation.sleeping_place_unavailable_after_checkout'),
                ]);
            }

            $booking = $this->updateBookingDates($extension);
            $this->updateBookingAmounts($extension);
            $this->updateStayDates($extension);
            $this->updateCalendarLocks($extension);
            $this->rescheduleCheckoutTasks($extension);
            $this->rescheduleCleaningTasks($extension);
            $this->rescheduleNotifications($extension);
            $this->rescheduleDepositReview($extension);
            $this->rescheduleReviewRequest($extension);
            $this->updateOccupancySnapshots($extension);

            $extension->forceFill([
                'status' => 'applied',
                'applied_at' => now(),
            ])->save();

            $this->events->record($extension->refresh(), 'extension_applied');
            $this->notifications->notifyGuestExtensionApplied($extension->refresh());
            $this->notifications->notifyHostExtensionApplied($extension->refresh());

            return $booking->refresh();
        });
    }

    public function updateBookingDates(BookingExtension $extension): Booking
    {
        $booking = $extension->booking()->firstOrFail();
        $checkIn = CarbonImmutable::parse($booking->check_in_date ?? $booking->check_in)->startOfDay();
        $newCheckOut = CarbonImmutable::parse($extension->new_check_out_date)->startOfDay();
        $nights = (int) $checkIn->diffInDays($newCheckOut);

        $booking->forceFill([
            'check_out' => $newCheckOut->toDateString(),
            'check_out_date' => $newCheckOut->toDateString(),
            'check_out_time' => $extension->new_check_out_time ?: $booking->check_out_time,
            'nights' => $nights,
            'nights_count' => $nights,
            'chargeable_days_count' => $nights,
            'calendar_days_count' => $nights + 1,
            'calendar_presence_days_count' => $nights + 1,
        ])->save();

        return $booking->refresh();
    }

    public function updateBookingAmounts(BookingExtension $extension): Booking
    {
        $booking = $extension->booking()->firstOrFail();

        $booking->forceFill([
            'subtotal' => $this->money((float) ($booking->subtotal ?: 0) + (float) $extension->accommodation_amount),
            'subtotal_amount' => $this->money((float) ($booking->subtotal_amount ?: 0) + (float) $extension->accommodation_amount),
            'accommodation_amount' => $this->money((float) ($booking->accommodation_amount ?: 0) + (float) $extension->accommodation_amount),
            'discount_amount' => $this->money((float) ($booking->discount_amount ?: 0) + (float) $extension->discount_amount),
            'service_fee' => $this->money((float) ($booking->service_fee ?: 0) + (float) $extension->service_fee_amount),
            'service_fee_amount' => $this->money((float) ($booking->service_fee_amount ?: 0) + (float) $extension->service_fee_amount),
            'cleaning_fee' => $this->money((float) ($booking->cleaning_fee ?: 0) + (float) $extension->cleaning_fee_amount),
            'cleaning_fee_amount' => $this->money((float) ($booking->cleaning_fee_amount ?: 0) + (float) $extension->cleaning_fee_amount),
            'deposit' => $this->money((float) ($booking->deposit ?: 0) + (float) $extension->additional_deposit_amount),
            'deposit_amount' => $this->money((float) ($booking->deposit_amount ?: 0) + (float) $extension->additional_deposit_amount),
            'total' => $this->money((float) ($booking->total ?: 0) + (float) $extension->total_payable),
            'total_amount' => $this->money((float) ($booking->total_amount ?: 0) + (float) $extension->total_payable),
            'total_payable' => $this->money((float) ($booking->total_payable ?: 0) + (float) $extension->total_payable),
            'host_payout_amount' => $this->money((float) ($booking->host_payout_amount ?: 0) + (float) $extension->host_payout_amount),
            'refundable_amount' => $this->money((float) ($booking->refundable_amount ?: 0) + (float) $extension->refundable_amount),
            'non_refundable_amount' => $this->money((float) ($booking->non_refundable_amount ?: 0) + (float) $extension->non_refundable_amount),
        ])->save();

        return $booking->refresh();
    }

    public function updateStayDates(BookingExtension $extension): ?BookingStay
    {
        $stay = $extension->bookingStay()->first() ?? $extension->booking?->stay()->first();

        if (! $stay instanceof BookingStay) {
            return null;
        }

        $checkIn = CarbonImmutable::parse($stay->check_in_date)->startOfDay();
        $newCheckOut = CarbonImmutable::parse($extension->new_check_out_date)->startOfDay();
        $nights = (int) $checkIn->diffInDays($newCheckOut);

        $stay->forceFill([
            'planned_check_out_date' => $newCheckOut->toDateString(),
            'planned_check_out_time' => $extension->new_check_out_time ?: $stay->planned_check_out_time,
            'nights_count' => $nights,
            'calendar_presence_days_count' => $nights + 1,
            'nights_remaining' => max(0, (int) now()->startOfDay()->diffInDays($newCheckOut, false)),
            'extension_requested' => false,
            'checkout_soon' => false,
            'status' => 'active',
        ])->save();

        return $stay->refresh();
    }

    public function updateCalendarLocks(BookingExtension $extension): void
    {
        $this->holds->convertHoldToBookingLocks($extension);
    }

    public function rescheduleCheckoutTasks(BookingExtension $extension): void
    {
        $extension->booking?->checkOut()
            ->update([
                'check_out_date' => $extension->new_check_out_date,
                'planned_check_out_time' => $extension->new_check_out_time,
                'status' => 'scheduled',
                'updated_at' => now(),
            ]);

        $this->events->record($extension, 'checkout_rescheduled');
    }

    public function rescheduleCleaningTasks(BookingExtension $extension): void
    {
        $this->events->record($extension, 'cleaning_rescheduled');
    }

    public function rescheduleNotifications(BookingExtension $extension): void
    {
        $this->events->record($extension, 'checkout_rescheduled');
    }

    public function rescheduleDepositReview(BookingExtension $extension): void
    {
        $this->events->record($extension, 'deposit_review_rescheduled');
    }

    public function rescheduleReviewRequest(BookingExtension $extension): void
    {
        $this->events->record($extension, 'review_request_rescheduled');
    }

    public function updateOccupancySnapshots(BookingExtension $extension): void
    {
        RoomCurrentOccupancySnapshot::query()
            ->where('room_id', $extension->room_id)
            ->update([
                'last_recalculated_at' => now(),
                'updated_at' => now(),
            ]);

        PropertyCurrentOccupancySnapshot::query()
            ->where('property_id', $extension->property_id)
            ->update([
                'last_recalculated_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function canApply(BookingExtension $extension): bool
    {
        $status = $extension->status instanceof \BackedEnum ? $extension->status->value : (string) $extension->status;

        if ($extension->requires_payment) {
            return in_array($status, ['paid', 'approved'], true)
                && $extension->payment_status === 'paid';
        }

        return in_array($status, ['approved', 'paid'], true);
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
