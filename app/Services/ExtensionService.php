<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingExtension;
use Carbon\Carbon;

class ExtensionService
{
    public function __construct(
        private AvailabilityService $availability,
        private BookingPriceCalculator $calculator,
    ) {}

    /**
     * @return array{success: bool, extension?: BookingExtension, error?: string}
     */
    public function request(Booking $booking, string $newCheckOut): array
    {
        $bed = $booking->bed;
        $currentCheckOut = $booking->check_out->toDateString();
        $newCheckOutDate = Carbon::parse($newCheckOut);

        if ($newCheckOutDate->lte($booking->check_out)) {
            return ['success' => false, 'error' => 'New check-out must be after current check-out.'];
        }

        if (! $this->availability->isAvailable($bed, $currentCheckOut, $newCheckOut)) {
            return ['success' => false, 'error' => 'Bed is not available for the extended dates.'];
        }

        $extraNights = $booking->check_out->diffInDays($newCheckOutDate);
        $extraPrice = $this->calculator->calculate($bed, $currentCheckOut, $newCheckOut);

        $extension = BookingExtension::create([
            'booking_id' => $booking->id,
            'original_check_out' => $currentCheckOut,
            'new_check_out' => $newCheckOut,
            'extra_nights' => $extraNights,
            'extra_amount' => $extraPrice['subtotal'],
            'discount_amount' => $extraPrice['discount'],
            'total_extra' => $extraPrice['subtotal'] - $extraPrice['discount'] + $extraPrice['service_fee'],
            'requires_host_approval' => ! $bed->instant_book,
            'status' => $bed->instant_book ? 'approved' : 'pending',
        ]);

        if ($bed->instant_book) {
            $this->applyExtension($booking, $extension);
        }

        return ['success' => true, 'extension' => $extension];
    }

    public function approve(BookingExtension $extension): void
    {
        $extension->update(['status' => 'approved']);
        $this->applyExtension($extension->booking, $extension);
    }

    public function reject(BookingExtension $extension, ?string $reason = null): void
    {
        $extension->update([
            'status' => 'rejected',
            'reject_reason' => $reason,
        ]);
    }

    private function applyExtension(Booking $booking, BookingExtension $extension): void
    {
        $booking->update([
            'check_out' => $extension->new_check_out,
            'nights' => $booking->nights + $extension->extra_nights,
            'calendar_days_count' => $booking->nights + $extension->extra_nights + 1,
            'subtotal' => (float) $booking->subtotal + (float) $extension->extra_amount,
            'discount_amount' => (float) $booking->discount_amount + (float) $extension->discount_amount,
            'total' => (float) $booking->total + (float) $extension->total_extra,
        ]);
    }
}
