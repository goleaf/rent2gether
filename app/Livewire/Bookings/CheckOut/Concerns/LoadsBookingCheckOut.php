<?php

namespace App\Livewire\Bookings\CheckOut\Concerns;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Services\CheckOut\BookingCheckOutService;

trait LoadsBookingCheckOut
{
    public ?int $bookingId = null;

    public ?int $checkOutId = null;

    public string $status = 'not_started';

    public function mount(Booking|int|null $booking = null, ?int $checkOutId = null): void
    {
        if ($booking instanceof Booking) {
            $this->bookingId = $booking->id;
        } elseif ($booking !== null) {
            $this->bookingId = (int) $booking;
        }

        $this->checkOutId = $checkOutId;
        $this->refreshCheckOutState();
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
                'deposit_amount',
                'deposit',
                'currency',
                'guest_checked_out_at',
                'host_confirmed_checkout_at',
                'checked_out_at',
            ])
            ->with([
                'guest:id,name',
                'host:id,name',
                'property:id,title,city,district',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
            ])
            ->find($this->bookingId);
    }

    protected function checkOut(): ?BookingCheckOut
    {
        if ($this->checkOutId) {
            return BookingCheckOut::query()
                ->with([
                    'booking',
                    'guest:id,name',
                    'host:id,name',
                    'room:id,title,room_number',
                    'sleepingPlace:id,display_name,place_number',
                    'checklistItems',
                    'issueReports',
                    'forgottenItems',
                    'depositDecision',
                    'inspectionTasks',
                    'booking.reviewRequests',
                ])
                ->find($this->checkOutId);
        }

        $booking = $this->booking();

        if (! $booking) {
            return null;
        }

        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);
        $this->checkOutId = $checkOut->id;

        return $checkOut->load([
            'booking',
            'guest:id,name',
            'host:id,name',
            'room:id,title,room_number',
            'sleepingPlace:id,display_name,place_number',
            'checklistItems',
            'issueReports',
            'forgottenItems',
            'depositDecision',
            'inspectionTasks',
            'booking.reviewRequests',
        ]);
    }

    protected function refreshCheckOutState(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut) {
            $this->checkOutId = $checkOut->id;
            $this->status = $checkOut->status;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkOutViewData(string $variant): array
    {
        $checkOut = $this->checkOut();

        return [
            'variant' => $variant,
            'booking' => $this->booking(),
            'checkOut' => $checkOut,
            'status' => $checkOut?->status ?? $this->status,
            'items' => $checkOut?->checklistItems ?? collect(),
            'issues' => $checkOut?->issueReports ?? collect(),
            'forgottenItems' => $checkOut?->forgottenItems ?? collect(),
            'depositDecision' => $checkOut?->depositDecision,
            'reviewRequests' => $checkOut?->booking?->reviewRequests ?? collect(),
            'canOfferExtension' => $checkOut ? app(BookingCheckOutService::class)->canOfferExtension($checkOut) : false,
        ];
    }
}
