<?php

namespace App\Livewire\Host\BookingRequests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestExpirationService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostBookingRequestsPage extends Component
{
    public string $filter = 'new';

    public int $perPage = 12;

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->perPage = 12;
    }

    public function loadMore(): void
    {
        $this->perPage += 12;
    }

    public function render(BookingRequestExpirationService $expiration): View
    {
        $host = auth()->user();

        if ($host instanceof User) {
            $expiration->expireDueRequestsForHost($host);
        }

        $hasMore = false;
        $requests = collect();

        if ($host instanceof User) {
            $requests = BookingRequest::query()
                ->select(['id', 'request_number', 'guest_user_id', 'sleeping_place_id', 'request_type', 'status', 'check_in_date', 'check_out_date', 'nights_count', 'total_amount', 'currency', 'trip_purpose', 'expires_at', 'created_at'])
                ->with(['guest:id,name,avatar_path', 'sleepingPlace:id,title,display_name'])
                ->forHost($host)
                ->when($this->filter === 'new', fn ($query) => $query->whereIn('status', [BookingRequest::STATUS_SUBMITTED, BookingRequest::STATUS_WAITING_HOST_RESPONSE]))
                ->when($this->filter === 'urgent', fn ($query) => $query->where('request_type', BookingRequest::TYPE_SAME_DAY_URGENT))
                ->when($this->filter === 'waiting_response', fn ($query) => $query->where('status', BookingRequest::STATUS_WAITING_GUEST_RESPONSE))
                ->when($this->filter === 'approved', fn ($query) => $query->whereIn('status', [BookingRequest::STATUS_APPROVED, BookingRequest::STATUS_APPROVED_WAITING_PAYMENT, BookingRequest::STATUS_CONVERTED_TO_BOOKING]))
                ->when($this->filter === 'rejected', fn ($query) => $query->where('status', BookingRequest::STATUS_REJECTED))
                ->when($this->filter === 'expired', fn ($query) => $query->where('status', BookingRequest::STATUS_EXPIRED))
                ->orderByDesc('created_at')
                ->limit($this->perPage + 1)
                ->get();
            $hasMore = $requests->count() > $this->perPage;
            $requests = $requests->take($this->perPage)->values();
        }

        return view('livewire.host.booking-requests.host-booking-requests-page', [
            'requests' => $requests,
            'filters' => ['new', 'urgent', 'waiting_response', 'approved', 'rejected', 'expired'],
            'hasMore' => $hasMore,
        ])->layout('layouts.app', [
            'title' => __('booking_requests.host_page.title'),
        ]);
    }
}
