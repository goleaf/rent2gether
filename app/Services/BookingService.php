<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Carbon;

class BookingService
{
    public function __construct(
        private AvailabilityService $availability,
        private BookingPriceCalculator $calculator,
    ) {}

    /**
     * @return array{success: bool, booking?: Booking, error?: string}
     */
    public function create(User $guest, Bed $bed, string $checkIn, string $checkOut, int $guestsCount = 1, ?string $message = null): array
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        if ($checkOutDate->lte($checkInDate)) {
            return ['success' => false, 'error' => 'Check-out must be after check-in.'];
        }

        $nights = $checkInDate->diffInDays($checkOutDate);

        if ($nights < $bed->min_nights) {
            return ['success' => false, 'error' => "Minimum stay is {$bed->min_nights} nights."];
        }

        if ($bed->max_nights && $nights > $bed->max_nights) {
            return ['success' => false, 'error' => "Maximum stay is {$bed->max_nights} nights."];
        }

        if ($guestsCount > $bed->max_guests) {
            return ['success' => false, 'error' => "This bed accepts maximum {$bed->max_guests} guests."];
        }

        if (! $this->availability->isAvailable($bed, $checkIn, $checkOut)) {
            return ['success' => false, 'error' => 'This bed is not available for the selected dates.'];
        }

        $price = $this->calculator->calculate($bed, $checkIn, $checkOut);
        $room = $bed->room;
        $property = $room->property;

        $freeCancelBefore = $bed->cancellation_policy
            ? $checkInDate->copy()->subHours($bed->cancellation_policy->freeCancelHours())
            : null;

        $booking = Booking::create([
            'bed_id' => $bed->id,
            'guest_id' => $guest->id,
            'host_id' => $property->user_id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'booking_type' => $bed->instant_book ? BookingType::Instant->value : BookingType::HostApproval->value,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests_count' => $guestsCount,
            'nights' => $price['nights'],
            'calendar_days_count' => $price['calendar_days'],
            'price_per_night' => $bed->price_per_night,
            'subtotal' => $price['subtotal'],
            'discount_amount' => $price['discount'],
            'cleaning_fee' => $price['cleaning_fee'],
            'deposit' => $price['deposit'],
            'service_fee' => $price['service_fee'],
            'total' => $price['total'],
            'currency' => 'EUR',
            'status' => $bed->instant_book ? BookingStatus::PendingPayment->value : BookingStatus::PendingHostConfirmation->value,
            'payment_status' => PaymentStatus::Unpaid->value,
            'cancellation_policy' => $bed->cancellation_policy?->value,
            'free_cancel_before' => $freeCancelBefore,
            'guest_message' => $message,
        ]);

        return ['success' => true, 'booking' => $booking];
    }

    public function confirmByHost(Booking $booking): bool
    {
        if ($booking->status !== BookingStatus::PendingHostConfirmation) {
            return false;
        }

        $booking->update(['status' => BookingStatus::PendingPayment->value]);

        return true;
    }

    public function rejectByHost(Booking $booking, ?string $reason = null): bool
    {
        if ($booking->status !== BookingStatus::PendingHostConfirmation) {
            return false;
        }

        $booking->update([
            'status' => BookingStatus::CancelledByHost->value,
            'cancel_reason' => $reason,
            'cancelled_by' => 'host',
        ]);

        return true;
    }

    public function markPaid(Booking $booking): void
    {
        $booking->update([
            'status' => BookingStatus::Confirmed->value,
            'payment_status' => PaymentStatus::Paid->value,
            'payment_paid_at' => now(),
        ]);
    }

    public function checkIn(Booking $booking): void
    {
        $booking->update([
            'status' => BookingStatus::CheckedIn->value,
            'guest_checked_in_at' => now(),
        ]);
    }

    public function checkOut(Booking $booking): void
    {
        $booking->update([
            'status' => BookingStatus::CheckedOut->value,
            'guest_checked_out_at' => now(),
        ]);
    }

    public function complete(Booking $booking): void
    {
        $booking->update(['status' => BookingStatus::Completed->value]);
    }
}
