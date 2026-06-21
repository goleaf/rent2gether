<?php

namespace App\Livewire\Bookings\NoShow;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Services\Bookings\BookingNoShowGuestResponseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class GuestNoShowPage extends Component
{
    public ?int $bookingId = null;

    public ?int $noShowId = null;

    public string $variant = 'page';

    public ?string $message = null;

    public ?string $newArrivalTime = '23:30';

    public function mount(?Booking $booking = null, ?BookingNoShow $noShow = null): void
    {
        $this->bookingId = $booking?->id ?? $noShow?->booking_id;
        $this->noShowId = $noShow?->id;
    }

    public function markOnTheWay(BookingNoShowGuestResponseService $responses): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user()) {
            return;
        }

        $responses->markOnTheWay(Auth::user(), $noShow, $this->message);
    }

    public function markLate(BookingNoShowGuestResponseService $responses): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user()) {
            return;
        }

        $responses->markLate(Auth::user(), $noShow, $this->newArrivalTime, $this->message);
    }

    public function markArrived(BookingNoShowGuestResponseService $responses): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user()) {
            return;
        }

        $responses->markArrived(Auth::user(), $noShow);
    }

    public function requestCancellation(BookingNoShowGuestResponseService $responses): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user()) {
            return;
        }

        $responses->requestCancellation(Auth::user(), $noShow, $this->message);
    }

    public function reportCheckInProblem(BookingNoShowGuestResponseService $responses): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user()) {
            return;
        }

        $responses->reportCheckInProblem(Auth::user(), $noShow, [
            'message' => $this->message,
        ]);
    }

    public function reportHostNotAnswering(BookingNoShowGuestResponseService $responses): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user()) {
            return;
        }

        $responses->reportHostNotAnswering(Auth::user(), $noShow, [
            'message' => $this->message,
        ]);
    }

    public function disputeNoShow(BookingNoShowGuestResponseService $responses): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user() || ! $this->message) {
            return;
        }

        $responses->disputeNoShow(Auth::user(), $noShow, $this->message);
    }

    public function render(): View
    {
        return view('livewire.bookings.no-show.card', [
            'booking' => $this->booking(),
            'noShow' => $this->noShow(),
            'noShows' => $this->noShows(),
            'variant' => $this->variant,
            'responseOptions' => $this->responseOptions(),
        ]);
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->select(['id', 'booking_number', 'guest_user_id', 'host_user_id', 'room_id', 'sleeping_place_id', 'check_in_date', 'check_out_date', 'check_in_time', 'arrival_time', 'currency', 'status'])
            ->with(['room:id,title', 'sleepingPlace:id,display_name,title'])
            ->find($this->bookingId);
    }

    protected function noShow(): ?BookingNoShow
    {
        if (! $this->noShowId) {
            return null;
        }

        return BookingNoShow::query()
            ->with([
                'booking:id,booking_number,status,check_in_date,check_out_date,currency',
                'room:id,title',
                'sleepingPlace:id,display_name,title',
                'contactAttempts' => fn ($query) => $query->latest('attempted_at')->limit(5),
                'guestResponses' => fn ($query) => $query->latest('id')->limit(5),
            ])
            ->find($this->noShowId);
    }

    /**
     * @return Collection<int, BookingNoShow>
     */
    protected function noShows(): Collection
    {
        if (! $this->bookingId) {
            return collect();
        }

        return BookingNoShow::query()
            ->where('booking_id', $this->bookingId)
            ->latest('id')
            ->limit(5)
            ->get();
    }

    /**
     * @return list<string>
     */
    protected function responseOptions(): array
    {
        return ['i_am_on_the_way', 'i_am_late', 'i_arrived', 'i_have_check_in_problem', 'host_not_answering', 'i_want_to_cancel', 'dispute_no_show'];
    }
}
