<?php

namespace App\Livewire\Host\Bookings;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostBookingsPage extends Component
{
    use BuildsBookingViewData;

    #[Locked]
    public ?int $hostUserId = null;

    public string $filter = 'waiting_confirmation';

    public int $limit = 10;

    public function mount(?int $hostUserId = null): void
    {
        $this->hostUserId = $hostUserId ?: auth()->id();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->limit = 10;
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    public function render(): View
    {
        $bookings = Booking::query()
            ->select([
                'id',
                'booking_number',
                'reference',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in_date',
                'check_out_date',
                'nights_count',
                'guests_count',
                'total_payable',
                'currency',
                'has_dispute',
                'has_complaint',
            ])
            ->with([
                'guest:id,name',
                'sleepingPlace:id,room_id,display_name,title',
                'room:id,name',
            ])
            ->where('host_user_id', $this->hostUserId ?: 0)
            ->when($this->filter !== 'all', fn ($query) => $this->applyFilter($query))
            ->orderByDesc('created_at')
            ->limit($this->limit)
            ->get()
            ->map(fn (Booking $booking): array => $this->bookingSummary($booking));

        return view('livewire.host.bookings.host-bookings-page', [
            'bookings' => $bookings,
            'filters' => $this->filters(),
        ]);
    }

    private function applyFilter($query): void
    {
        match ($this->filter) {
            'waiting_confirmation' => $query->whereIn('status', ['waiting_host_confirmation', 'awaiting_host_approval']),
            'waiting_payment' => $query->whereIn('status', ['waiting_payment', 'awaiting_payment']),
            'confirmed' => $query->whereIn('status', ['confirmed', 'paid', 'ready_for_check_in']),
            'today_check_in' => $query->whereDate('check_in_date', today()),
            'current_stays' => $query->whereIn('status', ['guest_checked_in', 'stay_in_progress', 'checked_in', 'in_progress']),
            'today_checkout' => $query->whereDate('check_out_date', today()),
            'disputes' => $query->where('has_dispute', true),
            'cancelled' => $query->whereIn('status', ['cancelled_by_guest', 'cancelled_by_host', 'payment_failed', 'rejected_by_host']),
            default => $query,
        };
    }

    /**
     * @return array<string, string>
     */
    private function filters(): array
    {
        return [
            'waiting_confirmation' => __('bookings.filters.waiting_confirmation'),
            'waiting_payment' => __('bookings.filters.waiting_payment'),
            'confirmed' => __('bookings.filters.confirmed'),
            'today_check_in' => __('bookings.filters.today_check_in'),
            'current_stays' => __('bookings.filters.current_stays'),
            'today_checkout' => __('bookings.filters.today_checkout'),
            'disputes' => __('bookings.filters.disputes'),
            'cancelled' => __('bookings.filters.cancelled'),
            'all' => __('bookings.filters.all'),
        ];
    }
}
