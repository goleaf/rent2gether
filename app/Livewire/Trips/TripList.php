<?php

namespace App\Livewire\Trips;

use App\Livewire\Trips\Concerns\LoadsTripBookings;
use App\Support\Trips\TripBookingPresenter;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class TripList extends Component
{
    use LoadsTripBookings;
    use WithPagination;

    #[Locked]
    public string $scope = 'upcoming';

    public function mount(?string $scope = null): void
    {
        $scope = $scope ?: 'upcoming';

        abort_unless(in_array($scope, ['upcoming', 'past', 'cancelled'], true), 404);

        $this->scope = $scope;
    }

    public function render(TripBookingPresenter $presenter): View
    {
        $bookings = $this->bookings();

        return view('livewire.trips.trip-list', [
            'bookings' => $bookings,
            'cards' => $bookings->getCollection()
                ->map(fn ($booking): array => [
                    'booking' => $booking,
                    ...$presenter->card($booking),
                ]),
            'scope' => $this->scope,
        ])->layout('layouts.app', [
            'title' => __('booking.trips.scopes.'.$this->scope.'.title'),
        ]);
    }

    private function bookings(): Paginator
    {
        $query = $this->tripBookingQuery()
            ->forGuest((int) auth()->id());

        match ($this->scope) {
            'past' => $query
                ->whereIn('status', TripBookingPresenter::pastStatuses())
                ->orderByDesc('check_out_date')
                ->orderByDesc('id'),
            'cancelled' => $query
                ->whereIn('status', TripBookingPresenter::cancelledStatuses())
                ->orderByDesc('updated_at')
                ->orderByDesc('id'),
            default => $query
                ->whereIn('status', TripBookingPresenter::upcomingStatuses())
                ->orderBy('check_in_date')
                ->orderBy('id'),
        };

        return $query->simplePaginate(6);
    }
}
