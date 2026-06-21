<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Models\Booking;
use App\Models\BookingGroupLink;
use App\Models\BookingQuote;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingGroupService
{
    public function __construct(
        private readonly BookingCreationService $creation,
        private readonly BookingStatusService $statuses,
    ) {}

    /**
     * @param  array<int, BookingQuote>  $bookingQuotes
     * @return Collection<int, Booking>
     */
    public function createGroupBooking(User $guest, array $bookingQuotes): Collection
    {
        return DB::transaction(function () use ($guest, $bookingQuotes): Collection {
            $groupNumber = $this->generateGroupBookingNumber();
            $bookings = collect();
            $mainBooking = null;

            foreach ($bookingQuotes as $quote) {
                $booking = $this->creation->createInstantBooking($guest, $quote, [
                    'booking_type' => BookingType::GroupChild->value,
                    'guest_group_type' => 'group_booking',
                ]);

                $mainBooking ??= $booking;

                if ($mainBooking->isNot($booking)) {
                    $booking->forceFill([
                        'parent_booking_id' => $mainBooking->id,
                    ])->save();
                }

                $this->addSleepingPlaceBooking($groupNumber, $booking->fresh(), $mainBooking->id);
                $bookings->push($booking->fresh());
            }

            return $bookings;
        });
    }

    public function addSleepingPlaceBooking(string $groupBookingNumber, Booking $booking, ?int $mainBookingId = null): BookingGroupLink
    {
        return BookingGroupLink::query()->create([
            'group_booking_number' => $groupBookingNumber,
            'main_booking_id' => $mainBookingId,
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'status' => 'active',
        ]);
    }

    /**
     * @return Collection<int, Booking>
     */
    public function cancelGroupBooking(string $groupBookingNumber, string $reason): Collection
    {
        return DB::transaction(function () use ($groupBookingNumber, $reason): Collection {
            $links = BookingGroupLink::query()
                ->with('booking')
                ->where('group_booking_number', $groupBookingNumber)
                ->get();

            return $links
                ->map(function (BookingGroupLink $link) use ($reason): ?Booking {
                    $booking = $link->booking;

                    if (! $booking instanceof Booking) {
                        return null;
                    }

                    $booking->forceFill([
                        'cancellation_reason' => $reason,
                    ])->save();

                    $this->statuses->transition($booking, BookingStatus::CancelledByGuestFlow->value, null, [
                        'event_key' => 'cancelled',
                        'reason_key' => $reason,
                    ]);

                    $link->forceFill(['status' => 'cancelled'])->save();

                    return $booking->fresh();
                })
                ->filter()
                ->values();
        });
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getGroupBookings(string $groupBookingNumber): Collection
    {
        return Booking::query()
            ->whereHas('groupLinks', fn ($query) => $query->where('group_booking_number', $groupBookingNumber))
            ->with(['sleepingPlace:id,room_id,display_name,title', 'room:id,name', 'property:id,title,name'])
            ->orderBy('id')
            ->get();
    }

    private function generateGroupBookingNumber(): string
    {
        $year = now()->format('Y');
        $count = BookingGroupLink::query()
            ->where('group_booking_number', 'like', "BG-{$year}-%")
            ->count() + 1;

        return 'BG-'.$year.'-'.str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
