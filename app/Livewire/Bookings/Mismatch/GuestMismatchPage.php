<?php

namespace App\Livewire\Bookings\Mismatch;

use App\Models\Booking;
use App\Models\BookingListingMismatchReport;
use App\Services\Bookings\ListingMismatchGuestResponseService;
use App\Services\Bookings\ListingMismatchService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class GuestMismatchPage extends Component
{
    public ?int $bookingId = null;

    public ?int $reportId = null;

    public string $variant = 'page';

    public string $mismatchType = 'missing_locker';

    public string $severity = 'medium';

    public ?string $guestDescription = null;

    public bool $guestWantsFix = true;

    public bool $guestWantsRelocation = false;

    public bool $guestWantsCancellation = false;

    public bool $guestWantsRefund = false;

    public bool $guestWantsCompensation = false;

    public ?string $guestMessage = null;

    public ?float $requestedAmount = null;

    public function mount(?Booking $booking = null, ?BookingListingMismatchReport $report = null): void
    {
        $this->bookingId = $booking?->id ?? $report?->booking_id;
        $this->reportId = $report?->id;
    }

    public function reportMismatch(ListingMismatchService $reports): void
    {
        $booking = $this->booking();

        if (! $booking || ! Auth::user()) {
            return;
        }

        $report = $reports->createFromGuestReport(Auth::user(), $booking, [
            'mismatch_type' => $this->mismatchType,
            'severity' => $this->severity,
            'guest_description' => $this->guestDescription,
            'guest_wants_fix' => $this->guestWantsFix,
            'guest_wants_relocation' => $this->guestWantsRelocation,
            'guest_wants_cancellation' => $this->guestWantsCancellation,
            'guest_wants_refund' => $this->guestWantsRefund,
            'guest_wants_compensation' => $this->guestWantsCompensation,
        ]);

        $this->reportId = $report->id;
    }

    public function requestRelocation(ListingMismatchGuestResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->requestRelocation(Auth::user(), $report));
    }

    public function requestCancellation(ListingMismatchGuestResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->requestCancellation(Auth::user(), $report));
    }

    public function requestRefund(ListingMismatchGuestResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->requestRefund(Auth::user(), $report, $this->requestedAmount ?: 0));
    }

    public function acceptResolution(ListingMismatchGuestResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->acceptResolution(Auth::user(), $report, [
            'accepted_resolution_type' => $report->resolution_type,
            'accepted_refund_amount' => $report->refund_amount,
            'message' => $this->guestMessage,
        ]));
    }

    public function rejectResolution(ListingMismatchGuestResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->rejectResolution(Auth::user(), $report, $this->guestMessage ?? ''));
    }

    public function openDispute(ListingMismatchGuestResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->openDispute(Auth::user(), $report, $this->guestMessage ?? ''));
    }

    public function render(): View
    {
        return view('livewire.bookings.mismatch.card', [
            'booking' => $this->booking(),
            'report' => $this->report(),
            'reports' => $this->reports(),
            'variant' => $this->variant,
            'types' => $this->types(),
            'severities' => $this->severities(),
        ]);
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->select(['id', 'booking_number', 'guest_user_id', 'host_user_id', 'property_id', 'room_id', 'sleeping_place_id', 'check_in_date', 'check_out_date', 'currency', 'status'])
            ->with(['room:id,title,name', 'sleepingPlace:id,display_name,title'])
            ->find($this->bookingId);
    }

    protected function report(): ?BookingListingMismatchReport
    {
        if (! $this->reportId) {
            return null;
        }

        return BookingListingMismatchReport::query()
            ->with([
                'booking:id,booking_number,status,currency',
                'room:id,title,name',
                'sleepingPlace:id,display_name,title',
                'items' => fn ($query) => $query->latest('id')->limit(8),
                'warnings' => fn ($query) => $query->latest('id')->limit(6),
                'media' => fn ($query) => $query->latest('id')->limit(6),
                'hostResponses' => fn ($query) => $query->latest('id')->limit(5),
                'resolutionOptions' => fn ($query) => $query->latest('id')->limit(5),
            ])
            ->find($this->reportId);
    }

    /**
     * @return Collection<int, BookingListingMismatchReport>
     */
    protected function reports(): Collection
    {
        if (! Auth::id()) {
            return collect();
        }

        return BookingListingMismatchReport::query()
            ->select(['id', 'mismatch_number', 'booking_id', 'guest_user_id', 'mismatch_type', 'severity', 'status', 'created_at'])
            ->where('guest_user_id', Auth::id())
            ->latest('id')
            ->limit(5)
            ->get();
    }

    /**
     * @return list<string>
     */
    protected function types(): array
    {
        return ['wrong_sleeping_place', 'missing_locker', 'missing_wifi', 'dirty_room', 'missing_hot_water', 'more_people_than_listed', 'wrong_address', 'photos_mismatch', 'other'];
    }

    /**
     * @return list<string>
     */
    protected function severities(): array
    {
        return ['low', 'medium', 'high', 'urgent', 'unsafe'];
    }

    private function withReport(callable $callback): void
    {
        $report = $this->report();

        if (! $report || ! Auth::user()) {
            return;
        }

        $callback($report);
    }
}
