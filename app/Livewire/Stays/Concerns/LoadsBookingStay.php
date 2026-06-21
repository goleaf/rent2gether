<?php

namespace App\Livewire\Stays\Concerns;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Services\Stays\BookingStayService;
use App\Services\Stays\GuestRoommatesPreviewService;
use App\Services\Stays\StayCompatibilityService;
use Illuminate\Support\Collection;

trait LoadsBookingStay
{
    public ?int $stayId = null;

    public ?int $bookingId = null;

    public function mount(BookingStay|Booking|int|null $stay = null, Booking|int|null $booking = null): void
    {
        if ($stay instanceof BookingStay) {
            $this->stayId = $stay->id;
            $this->bookingId = $stay->booking_id;
        } elseif ($stay instanceof Booking) {
            $this->bookingId = $stay->id;
        } elseif ($stay !== null) {
            $this->stayId = (int) $stay;
        }

        if ($booking instanceof Booking) {
            $this->bookingId = $booking->id;
        } elseif ($booking !== null) {
            $this->bookingId = (int) $booking;
        }
    }

    protected function stay(): ?BookingStay
    {
        if ($this->stayId) {
            return $this->stayQuery()->find($this->stayId);
        }

        if ($this->bookingId) {
            $booking = Booking::query()
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
                    'nights_count',
                    'calendar_presence_days_count',
                    'deposit_amount',
                    'has_complaint',
                    'has_open_maintenance',
                    'has_deposit_issue',
                    'checked_in_at',
                    'checked_out_at',
                    'closed_at',
                ])
                ->find($this->bookingId);

            if (! $booking) {
                return null;
            }

            $stay = app(BookingStayService::class)->createForBooking($booking);
            $this->stayId = $stay->id;

            return $this->stayQuery()->find($stay->id);
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function stayViewData(string $variant): array
    {
        $stay = $this->stay();

        return [
            'variant' => $variant,
            'stay' => $stay,
            'summary' => $stay ? $this->summary($stay) : null,
            'roommates' => $this->roommates($stay),
            'warnings' => $this->warnings($stay),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(BookingStay $stay): array
    {
        return [
            'stay_number' => $stay->stay_number,
            'status' => __('stays.statuses.'.$stay->status),
            'status_key' => $stay->status,
            'property' => $stay->property?->title,
            'room' => $stay->room?->title,
            'sleeping_place' => $stay->sleepingPlace?->display_name ?: $stay->sleepingPlace?->place_number,
            'dates' => trim(($stay->check_in_date?->format('M j') ?: '').' - '.($stay->planned_check_out_date?->format('M j') ?: '')),
            'nights_remaining' => $stay->nights_remaining,
            'payment_status' => $stay->payment_status ? __('bookings.payment_statuses.'.$stay->payment_status) : null,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function roommates(?BookingStay $stay): Collection
    {
        if (! $stay || ! auth()->user()) {
            return collect();
        }

        return app(GuestRoommatesPreviewService::class)->getRoommatesForBooking(auth()->user(), $stay->booking);
    }

    /**
     * @return Collection<int, string>
     */
    private function warnings(?BookingStay $stay): Collection
    {
        if (! $stay || ! auth()->user() || ! $stay->room) {
            return collect();
        }

        return app(StayCompatibilityService::class)->buildRoommateCompatibilityWarnings(auth()->user(), $stay->room);
    }

    private function stayQuery()
    {
        return BookingStay::query()
            ->with([
                'booking:id,guest_user_id,host_user_id,room_id',
                'property:id,title,city',
                'room:id,property_id,user_id,title',
                'sleepingPlace:id,display_name,place_number',
                'occupants',
                'visibilityPreference',
            ]);
    }
}
