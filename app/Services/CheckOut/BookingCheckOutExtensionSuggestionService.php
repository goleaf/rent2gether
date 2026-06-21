<?php

namespace App\Services\CheckOut;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\SleepingPlaceCalendarDay;
use Carbon\CarbonImmutable;

class BookingCheckOutExtensionSuggestionService
{
    public function canSuggestExtension(Booking $booking): bool
    {
        $date = CarbonImmutable::parse($booking->check_out_date ?: $booking->check_out)->toDateString();

        $hasCalendarBlock = SleepingPlaceCalendarDay::query()
            ->where('sleeping_place_id', $booking->sleeping_place_id)
            ->whereDate('date', $date)
            ->whereIn('status', ['booked', 'pending_payment', 'pending_host_confirmation', 'cleaning', 'repair', 'blocked', 'unavailable'])
            ->exists();

        if ($hasCalendarBlock) {
            return false;
        }

        return ! Booking::query()
            ->where('sleeping_place_id', $booking->sleeping_place_id)
            ->where('id', '!=', $booking->id)
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

    /**
     * @return array<string, mixed>|null
     */
    public function suggestExtensionIfAvailable(BookingCheckOut $checkOut): ?array
    {
        $booking = $checkOut->booking()->firstOrFail();

        if (! $this->canSuggestExtension($booking)) {
            return null;
        }

        return [
            'available' => true,
            'booking_id' => $booking->id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'from_date' => $booking->check_out_date?->toDateString() ?? (string) $booking->check_out,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getExtensionAvailabilitySummary(BookingCheckOut $checkOut): array
    {
        $suggestion = $this->suggestExtensionIfAvailable($checkOut);

        return [
            'can_extend' => $suggestion !== null,
            'message_key' => $suggestion ? 'check_out.messages.extension_available' : 'check_out.messages.extension_not_available',
            'suggestion' => $suggestion,
        ];
    }
}
