<?php

namespace App\Livewire\Host\NoShow;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Services\Bookings\BookingNoShowDecisionService;
use App\Services\Bookings\BookingNoShowService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostNoShowDetailsSheet extends Component
{
    public ?int $bookingId = null;

    public ?int $noShowId = null;

    public string $variant = 'details_sheet';

    public ?string $hostComment = null;

    public function mount(?Booking $booking = null, ?BookingNoShow $noShow = null): void
    {
        $this->bookingId = $booking?->id ?? $noShow?->booking_id;
        $this->noShowId = $noShow?->id;
    }

    public function reportNoShow(BookingNoShowService $noShows): void
    {
        $booking = $this->booking();

        if (! $booking || ! Auth::user()) {
            return;
        }

        $noShow = $noShows->createFromHostReport(Auth::user(), $booking, [
            'reason_key' => 'host_reported_guest_absent',
            'host_comment' => $this->hostComment,
        ]);

        $this->noShowId = $noShow->id;
    }

    public function confirmNoShow(BookingNoShowDecisionService $decisions): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user()) {
            return;
        }

        $confirmed = $decisions->confirmNoShow(Auth::user(), $noShow);
        $this->noShowId = $confirmed->id;
    }

    public function rejectNoShow(BookingNoShowDecisionService $decisions): void
    {
        $noShow = $this->noShow();

        if (! $noShow || ! Auth::user()) {
            return;
        }

        $rejected = $decisions->rejectNoShow(Auth::user(), $noShow, 'guest_arrived');
        $this->noShowId = $rejected->id;
    }

    public function cancelNoShow(BookingNoShowService $noShows): void
    {
        $noShow = $this->noShow();

        if (! $noShow) {
            return;
        }

        $cancelled = $noShows->cancelNoShow($noShow, 'host_cancelled_report');
        $this->noShowId = $cancelled->id;
    }

    public function render(): View
    {
        return view('livewire.host.no-show.card', [
            'booking' => $this->booking(),
            'noShow' => $this->noShow(),
            'noShows' => $this->noShows(),
            'variant' => $this->variant,
        ]);
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->select(['id', 'booking_number', 'guest_user_id', 'host_user_id', 'room_id', 'sleeping_place_id', 'check_in_date', 'check_out_date', 'check_in_time', 'arrival_time', 'currency', 'status'])
            ->with(['guest:id,name', 'room:id,title', 'sleepingPlace:id,display_name,title'])
            ->find($this->bookingId);
    }

    protected function noShow(): ?BookingNoShow
    {
        if (! $this->noShowId) {
            return null;
        }

        return BookingNoShow::query()
            ->with([
                'guest:id,name',
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
        if (! Auth::id()) {
            return collect();
        }

        return BookingNoShow::query()
            ->with(['guest:id,name', 'room:id,title', 'sleepingPlace:id,display_name,title'])
            ->where('host_user_id', Auth::id())
            ->latest('id')
            ->limit(10)
            ->get();
    }
}
