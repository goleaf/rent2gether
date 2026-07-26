<?php

namespace App\Livewire\Bookings\Dates;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Bookings\BookingDateSelectionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AvailableCheckoutDates extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkInDate = '';

    public function mount(int|SleepingPlace $sleepingPlace, string $checkInDate = ''): void
    {
        $this->sleepingPlaceId = $sleepingPlace instanceof SleepingPlace ? $sleepingPlace->id : $sleepingPlace;
        $this->checkInDate = $checkInDate;
    }

    public function render(): View
    {
        $calendar = [
            'available_checkout_dates' => [],
            'unavailable_checkout_dates' => [],
            'earliest_checkout_date' => null,
            'latest_checkout_date' => null,
            'min_checkout_date' => null,
            'max_checkout_date' => null,
            'nearest_available_ranges' => [],
            'similar_sleeping_places' => [],
            'same_host_alternatives' => [],
            'neighbor_room_alternatives' => [],
        ];
        $guest = auth()->user();

        if ($guest instanceof User && $this->checkInDate !== '') {
            $place = SleepingPlace::query()
                ->select([
                    'id',
                    'room_id',
                    'property_id',
                    'user_id',
                    'status',
                    'title',
                    'display_name',
                    'place_type',
                    'base_price',
                    'base_price_per_night',
                    'currency',
                    'min_nights',
                    'max_nights',
                    'requires_host_approval',
                    'instant_booking_enabled',
                ])
                ->with([
                    'room:id,property_id,status',
                    'property:id,status',
                    'calendarSettings',
                ])
                ->findOrFail($this->sleepingPlaceId);
            $calendar = app(BookingDateSelectionService::class)
                ->checkoutCalendar($guest, $place, $this->checkInDate);
        }

        return view('livewire.bookings.dates.available-checkout-dates', [
            'dates' => $calendar['available_checkout_dates'],
            'earliestCheckoutDate' => $calendar['earliest_checkout_date'],
            'latestCheckoutDate' => $calendar['latest_checkout_date'],
            'minCheckoutDate' => $calendar['min_checkout_date'],
            'maxCheckoutDate' => $calendar['max_checkout_date'],
            'unavailableCheckoutDates' => $calendar['unavailable_checkout_dates'],
        ]);
    }
}
