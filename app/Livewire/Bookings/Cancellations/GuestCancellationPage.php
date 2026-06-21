<?php

namespace App\Livewire\Bookings\Cancellations;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;
use App\Services\Bookings\BookingCancellationPreviewService;
use App\Services\Bookings\BookingCancellationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class GuestCancellationPage extends Component
{
    public ?int $bookingId = null;

    public ?int $previewId = null;

    public ?int $cancellationId = null;

    public string $variant = 'page';

    public string $reasonKey = 'changed_plans';

    public string $cancellationType = 'guest_fault';

    public ?string $comment = null;

    public function mount(?Booking $booking = null, ?BookingCancellationPreview $preview = null, ?BookingCancellation $cancellation = null): void
    {
        $this->bookingId = $booking?->id ?? $preview?->booking_id ?? $cancellation?->booking_id;
        $this->previewId = $preview?->id;
        $this->cancellationId = $cancellation?->id;
    }

    public function createPreview(BookingCancellationPreviewService $previews): void
    {
        $booking = $this->booking();

        if (! $booking || ! Auth::user()) {
            return;
        }

        $preview = $previews->createPreview(Auth::user(), $booking, [
            'cancellation_type' => $this->cancellationType,
            'reason_key' => $this->reasonKey,
            'comment' => $this->comment,
        ]);

        $this->previewId = $preview->id;
    }

    public function confirmCancellation(BookingCancellationService $cancellations): void
    {
        $preview = $this->preview();

        if (! $preview || ! Auth::user()) {
            return;
        }

        $cancellation = $cancellations->confirmCancellation(Auth::user(), $preview);
        $this->cancellationId = $cancellation->id;
    }

    public function render(): View
    {
        return view('livewire.bookings.cancellations.card', [
            'booking' => $this->booking(),
            'preview' => $this->preview(),
            'cancellation' => $this->cancellation(),
            'cancellations' => $this->cancellations(),
            'variant' => $this->variant,
            'reasonOptions' => $this->reasonOptions(),
        ]);
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->select(['id', 'guest_user_id', 'host_user_id', 'room_id', 'sleeping_place_id', 'check_in_date', 'check_out_date', 'currency', 'total_payable', 'status'])
            ->with(['room:id,title', 'sleepingPlace:id,display_name,title'])
            ->find($this->bookingId);
    }

    protected function preview(): ?BookingCancellationPreview
    {
        if (! $this->previewId) {
            return null;
        }

        return BookingCancellationPreview::query()
            ->with(['booking:id', 'sleepingPlace:id,display_name,title'])
            ->find($this->previewId);
    }

    protected function cancellation(): ?BookingCancellation
    {
        if (! $this->cancellationId) {
            return null;
        }

        return BookingCancellation::query()
            ->with(['refundLines', 'sleepingPlace:id,display_name,title'])
            ->find($this->cancellationId);
    }

    /**
     * @return Collection<int, BookingCancellation>
     */
    protected function cancellations(): Collection
    {
        if (! $this->bookingId) {
            return collect();
        }

        return BookingCancellation::query()
            ->where('booking_id', $this->bookingId)
            ->latest('id')
            ->limit(5)
            ->get();
    }

    /**
     * @return list<string>
     */
    protected function reasonOptions(): array
    {
        return ['changed_plans', 'found_other_place', 'wrong_dates', 'too_expensive', 'housing_problem', 'host_unresponsive', 'listing_mismatch', 'maintenance', 'other'];
    }
}
