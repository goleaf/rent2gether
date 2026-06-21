<?php

namespace App\Livewire\Host\HostUnresponsive;

use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Services\Bookings\HostUnresponsiveDecisionService;
use App\Services\Bookings\HostUnresponsiveHostResponseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostUnresponsiveDetailsSheet extends Component
{
    public ?int $bookingId = null;

    public ?int $caseId = null;

    public string $variant = 'details_sheet';

    public ?string $hostMessage = null;

    public function mount(?Booking $booking = null, ?BookingHostUnresponsiveCase $case = null): void
    {
        $this->bookingId = $booking?->id ?? $case?->booking_id;
        $this->caseId = $case?->id;
    }

    public function markAvailable(HostUnresponsiveHostResponseService $responses): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $responses->markAvailable(Auth::user(), $case, $this->hostMessage));
    }

    public function sendInstruction(HostUnresponsiveHostResponseService $responses): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $responses->sendInstruction(Auth::user(), $case, $this->hostMessage ?? ''));
    }

    public function sendAccessDetails(HostUnresponsiveHostResponseService $responses): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $responses->sendAccessDetails(Auth::user(), $case, [
            'message' => $this->hostMessage,
            'door_code_provided' => true,
        ]));
    }

    public function denyUnresponsive(HostUnresponsiveHostResponseService $responses): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $responses->denyUnresponsive(Auth::user(), $case, $this->hostMessage ?? ''));
    }

    public function markAccessResolved(HostUnresponsiveDecisionService $decisions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $decisions->markAccessResolved($case));
    }

    public function confirmUnresolved(HostUnresponsiveDecisionService $decisions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $decisions->confirmHostUnresponsive($case, Auth::user()));
    }

    public function render(): View
    {
        return view('livewire.host.host-unresponsive.card', [
            'booking' => $this->booking(),
            'case' => $this->case(),
            'cases' => $this->cases(),
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

    protected function case(): ?BookingHostUnresponsiveCase
    {
        if (! $this->caseId) {
            return null;
        }

        return BookingHostUnresponsiveCase::query()
            ->with([
                'guest:id,name',
                'booking:id,booking_number,status,check_in_date,check_out_date,currency',
                'room:id,title',
                'sleepingPlace:id,display_name,title',
                'contactAttempts' => fn ($query) => $query->latest('attempted_at')->limit(5),
                'hostResponses' => fn ($query) => $query->latest('id')->limit(5),
                'representativeResponses' => fn ($query) => $query->latest('id')->limit(5),
            ])
            ->find($this->caseId);
    }

    /**
     * @return Collection<int, BookingHostUnresponsiveCase>
     */
    protected function cases(): Collection
    {
        if (! Auth::id()) {
            return collect();
        }

        return BookingHostUnresponsiveCase::query()
            ->with(['guest:id,name', 'room:id,title', 'sleepingPlace:id,display_name,title'])
            ->where('host_user_id', Auth::id())
            ->latest('id')
            ->limit(10)
            ->get();
    }

    private function withCase(callable $callback): void
    {
        $case = $this->case();

        if (! $case || ! Auth::user()) {
            return;
        }

        $callback($case);
    }
}
