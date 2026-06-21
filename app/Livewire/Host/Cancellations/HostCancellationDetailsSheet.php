<?php

namespace App\Livewire\Host\Cancellations;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Services\Bookings\BookingCancellationPreviewService;
use App\Services\Bookings\BookingCancellationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostCancellationDetailsSheet extends Component
{
    public ?int $bookingId = null;

    public ?int $cancellationId = null;

    public string $variant = 'details_sheet';

    public string $reasonKey = 'maintenance';

    public ?string $hostComment = null;

    public function mount(?Booking $booking = null, ?BookingCancellation $cancellation = null): void
    {
        $this->bookingId = $booking?->id ?? $cancellation?->booking_id;
        $this->cancellationId = $cancellation?->id;
    }

    public function cancelBooking(BookingCancellationPreviewService $previews, BookingCancellationService $cancellations): void
    {
        $booking = $this->booking();

        if (! $booking || ! Auth::user()) {
            return;
        }

        $preview = $previews->createPreview(Auth::user(), $booking, [
            'requested_by_type' => 'host',
            'cancellation_type' => 'host_fault',
            'reason_key' => $this->reasonKey,
            'comment' => $this->hostComment,
        ]);
        $cancellation = $cancellations->confirmCancellation(Auth::user(), $preview);

        $this->cancellationId = $cancellation->id;
    }

    public function render(): View
    {
        return view('livewire.host.cancellations.card', [
            'booking' => $this->booking(),
            'cancellation' => $this->cancellation(),
            'cancellations' => $this->cancellations(),
            'variant' => $this->variant,
        ]);
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->select(['id', 'guest_user_id', 'host_user_id', 'room_id', 'sleeping_place_id', 'check_in_date', 'check_out_date', 'currency', 'total_payable', 'status'])
            ->with(['guest:id,name', 'room:id,title', 'sleepingPlace:id,display_name,title'])
            ->find($this->bookingId);
    }

    protected function cancellation(): ?BookingCancellation
    {
        if (! $this->cancellationId) {
            return null;
        }

        return BookingCancellation::query()
            ->with(['guest:id,name', 'room:id,title', 'sleepingPlace:id,display_name,title', 'refundLines'])
            ->find($this->cancellationId);
    }

    /**
     * @return Collection<int, BookingCancellation>
     */
    protected function cancellations(): Collection
    {
        if (! Auth::id()) {
            return collect();
        }

        return BookingCancellation::query()
            ->with(['guest:id,name', 'room:id,title', 'sleepingPlace:id,display_name,title'])
            ->where('host_user_id', Auth::id())
            ->latest('id')
            ->limit(10)
            ->get();
    }
}
