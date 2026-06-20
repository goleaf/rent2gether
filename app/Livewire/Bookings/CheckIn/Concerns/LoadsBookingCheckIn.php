<?php

namespace App\Livewire\Bookings\CheckIn\Concerns;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Services\CheckIn\BookingCheckInService;

trait LoadsBookingCheckIn
{
    public ?int $bookingId = null;

    public ?int $checkInId = null;

    public string $status = 'not_started';

    public function mount(Booking|int|null $booking = null, ?int $checkInId = null): void
    {
        if ($booking instanceof Booking) {
            $this->bookingId = $booking->id;
        } elseif ($booking !== null) {
            $this->bookingId = (int) $booking;
        }

        $this->checkInId = $checkInId;
        $this->refreshCheckInState();
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->select([
                'id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'check_out_time',
                'arrival_time',
                'check_in_instructions',
                'guest_checked_in_at',
                'host_confirmed_checkin_at',
                'checked_in_at',
            ])
            ->with([
                'guest:id,name',
                'host:id,name,phone,phone_verified,email',
                'property:id,title,city,district,address_line_1,house_number,apartment_number,show_exact_address_after_confirmation,show_exact_address_after_payment',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
            ])
            ->find($this->bookingId);
    }

    protected function checkIn(): ?BookingCheckIn
    {
        if ($this->checkInId) {
            return BookingCheckIn::query()
                ->with(['booking', 'guest:id,name', 'host:id,name', 'room:id,title,room_number', 'sleepingPlace:id,display_name,place_number', 'checklistItems', 'problemReports', 'alerts'])
                ->find($this->checkInId);
        }

        $booking = $this->booking();

        if (! $booking) {
            return null;
        }

        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $this->checkInId = $checkIn->id;

        return $checkIn->load(['booking', 'guest:id,name', 'host:id,name', 'room:id,title,room_number', 'sleepingPlace:id,display_name,place_number', 'checklistItems', 'problemReports', 'alerts']);
    }

    protected function refreshCheckInState(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn) {
            $this->checkInId = $checkIn->id;
            $this->status = $checkIn->status;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkInViewData(string $variant): array
    {
        $checkIn = $this->checkIn();

        return [
            'variant' => $variant,
            'booking' => $this->booking(),
            'checkIn' => $checkIn,
            'status' => $checkIn?->status ?? $this->status,
            'items' => $checkIn?->checklistItems ?? collect(),
            'reports' => $checkIn?->problemReports ?? collect(),
            'alerts' => $checkIn?->alerts ?? collect(),
        ];
    }
}
