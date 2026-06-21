<?php

namespace App\Livewire\Host\Mismatch;

use App\Models\BookingListingMismatchReport;
use App\Models\SleepingPlace;
use App\Services\Bookings\ListingMismatchHostResponseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostMismatchDetailsSheet extends Component
{
    public ?int $reportId = null;

    public string $variant = 'details_sheet';

    public ?string $hostMessage = null;

    public ?float $amount = null;

    public ?int $alternativeSleepingPlaceId = null;

    public function mount(?BookingListingMismatchReport $report = null): void
    {
        $this->reportId = $report?->id;
    }

    public function acceptProblem(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->accept(Auth::user(), $report, $this->hostMessage));
    }

    public function denyProblem(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->deny(Auth::user(), $report, $this->hostMessage ?? ''));
    }

    public function askMoreEvidence(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->askForMoreEvidence(Auth::user(), $report, $this->hostMessage ?? ''));
    }

    public function offerFix(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->offerFix(Auth::user(), $report, $this->hostMessage ?? ''));
    }

    public function offerCleaning(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->offerCleaning(Auth::user(), $report, $this->hostMessage ?? ''));
    }

    public function offerRepair(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->offerRepair(Auth::user(), $report, $this->hostMessage ?? ''));
    }

    public function offerRelocation(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(function (BookingListingMismatchReport $report) use ($responses): void {
            $place = $this->alternativeSleepingPlaceId
                ? SleepingPlace::query()->find($this->alternativeSleepingPlaceId)
                : SleepingPlace::query()->where('property_id', $report->property_id)->where('id', '!=', $report->sleeping_place_id)->first();

            if ($place instanceof SleepingPlace) {
                $responses->offerRelocation(Auth::user(), $report, $place);
            }
        });
    }

    public function offerRefund(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->offerRefund(Auth::user(), $report, $this->amount ?: 0));
    }

    public function offerCompensation(ListingMismatchHostResponseService $responses): void
    {
        $this->withReport(fn (BookingListingMismatchReport $report) => $responses->offerCompensation(Auth::user(), $report, $this->amount ?: 0));
    }

    public function render(): View
    {
        return view('livewire.host.mismatch.card', [
            'report' => $this->report(),
            'reports' => $this->reports(),
            'variant' => $this->variant,
        ]);
    }

    protected function report(): ?BookingListingMismatchReport
    {
        if (! $this->reportId) {
            return null;
        }

        return BookingListingMismatchReport::query()
            ->with([
                'guest:id,name',
                'booking:id,booking_number,status,currency',
                'room:id,title,name',
                'sleepingPlace:id,display_name,title',
                'items' => fn ($query) => $query->latest('id')->limit(8),
                'warnings' => fn ($query) => $query->latest('id')->limit(6),
                'media' => fn ($query) => $query->latest('id')->limit(6),
                'hostResponses' => fn ($query) => $query->latest('id')->limit(5),
                'guestResponses' => fn ($query) => $query->latest('id')->limit(5),
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
            ->with(['guest:id,name', 'sleepingPlace:id,display_name,title'])
            ->where('host_user_id', Auth::id())
            ->latest('id')
            ->limit(10)
            ->get();
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
