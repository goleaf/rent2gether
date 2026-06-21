<?php

namespace App\Livewire\Bookings\HostUnresponsive;

use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Services\Bookings\HostUnresponsiveGuestActionService;
use App\Services\Bookings\HostUnresponsiveService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class GuestHostUnresponsivePage extends Component
{
    public ?int $bookingId = null;

    public ?int $caseId = null;

    public string $variant = 'page';

    public string $caseType = 'check_in_no_response';

    public string $reasonKey = 'host_not_answering_messages';

    public ?string $message = null;

    public ?string $locationNote = null;

    public function mount(?Booking $booking = null, ?BookingHostUnresponsiveCase $case = null): void
    {
        $this->bookingId = $booking?->id ?? $case?->booking_id;
        $this->caseId = $case?->id;
    }

    public function reportHostNotAnswering(HostUnresponsiveService $cases): void
    {
        $booking = $this->booking();

        if (! $booking || ! Auth::user()) {
            return;
        }

        $case = $cases->createFromGuestReport(Auth::user(), $booking, [
            'case_type' => $this->caseType,
            'reason_key' => $this->reasonKey,
            'message' => $this->message,
            'guest_location_note' => $this->locationNote,
        ]);

        $this->caseId = $case->id;
    }

    public function markAtAddress(HostUnresponsiveGuestActionService $actions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $actions->markAtAddress(Auth::user(), $case, $this->locationNote));
    }

    public function markWaitingOutside(HostUnresponsiveGuestActionService $actions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $actions->markWaitingOutside(Auth::user(), $case, $this->locationNote));
    }

    public function markFeelsUnsafe(HostUnresponsiveGuestActionService $actions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $actions->markFeelsUnsafe(Auth::user(), $case, $this->locationNote));
    }

    public function requestCancellation(HostUnresponsiveGuestActionService $actions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $actions->requestCancellation(Auth::user(), $case));
    }

    public function requestRelocation(HostUnresponsiveGuestActionService $actions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $actions->requestRelocation(Auth::user(), $case));
    }

    public function continueWaiting(HostUnresponsiveGuestActionService $actions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $actions->continueWaiting(Auth::user(), $case));
    }

    public function openDispute(HostUnresponsiveGuestActionService $actions): void
    {
        $this->withCase(fn (BookingHostUnresponsiveCase $case) => $actions->openDispute(Auth::user(), $case));
    }

    public function render(): View
    {
        return view('livewire.bookings.host-unresponsive.card', [
            'booking' => $this->booking(),
            'case' => $this->case(),
            'cases' => $this->cases(),
            'variant' => $this->variant,
            'caseTypes' => $this->caseTypes(),
            'reasons' => $this->reasons(),
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

    protected function case(): ?BookingHostUnresponsiveCase
    {
        if (! $this->caseId) {
            return null;
        }

        return BookingHostUnresponsiveCase::query()
            ->with([
                'booking:id,booking_number,status,check_in_date,check_out_date,currency',
                'room:id,title',
                'sleepingPlace:id,display_name,title',
                'contactAttempts' => fn ($query) => $query->latest('attempted_at')->limit(5),
                'guestActions' => fn ($query) => $query->latest('id')->limit(5),
            ])
            ->find($this->caseId);
    }

    /**
     * @return Collection<int, BookingHostUnresponsiveCase>
     */
    protected function cases(): Collection
    {
        if (! $this->bookingId) {
            return collect();
        }

        return BookingHostUnresponsiveCase::query()
            ->where('booking_id', $this->bookingId)
            ->latest('id')
            ->limit(5)
            ->get();
    }

    /**
     * @return list<string>
     */
    protected function caseTypes(): array
    {
        return ['pre_check_in_no_response', 'check_in_no_response', 'access_problem_no_response', 'night_entry_no_response', 'self_check_in_failed', 'representative_no_response', 'during_stay_urgent_no_response', 'checkout_no_response', 'other'];
    }

    /**
     * @return list<string>
     */
    protected function reasons(): array
    {
        return ['host_not_answering_messages', 'host_not_answering_calls', 'instruction_missing', 'door_code_not_working', 'key_not_found', 'representative_not_answering', 'guest_waiting_outside', 'self_check_in_failed', 'unsafe_to_wait', 'other'];
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
