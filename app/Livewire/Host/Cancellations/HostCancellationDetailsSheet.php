<?php

namespace App\Livewire\Host\Cancellations;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Services\Bookings\BookingCancellationPreviewService;
use App\Services\Bookings\BookingCancellationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostCancellationDetailsSheet extends Component
{
    #[Locked]
    public ?int $bookingId = null;

    #[Locked]
    public ?int $cancellationId = null;

    public string $variant = 'details_sheet';

    public string $reasonKey = 'maintenance';

    public ?string $hostComment = null;

    public function mount(?Booking $booking = null, ?BookingCancellation $cancellation = null): void
    {
        if ($booking instanceof Booking) {
            $this->authorizeHostContext($booking->host_user_id);
        }

        if ($cancellation instanceof BookingCancellation) {
            $this->authorizeHostContext($cancellation->host_user_id);
        }

        $this->bookingId = $booking?->id ?? $cancellation?->booking_id;
        $this->cancellationId = $cancellation?->id;
    }

    public function cancelBooking(BookingCancellationPreviewService $previews, BookingCancellationService $cancellations): void
    {
        $booking = $this->booking;

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
        unset($this->cancellation, $this->cancellations);
    }

    public function render(): View
    {
        return view('livewire.host.cancellations.card', [
            'booking' => $this->booking,
            'cancellation' => $this->cancellation,
            'cancellations' => $this->cancellations,
            'variant' => $this->variant,
        ]);
    }

    #[Computed]
    public function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        $hostId = Auth::id();

        if (! $hostId) {
            abort(403);
        }

        $booking = Booking::query()
            ->select(['id', 'guest_user_id', 'host_user_id', 'room_id', 'sleeping_place_id', 'check_in_date', 'check_out_date', 'currency', 'total_payable', 'status'])
            ->with(['guest:id,name', 'room:id,title', 'sleepingPlace:id,display_name,title'])
            ->where('host_user_id', $hostId)
            ->find($this->bookingId);

        if (! $booking) {
            abort(403);
        }

        return $booking;
    }

    #[Computed]
    public function cancellation(): ?BookingCancellation
    {
        if (! $this->cancellationId) {
            return null;
        }

        $hostId = Auth::id();

        if (! $hostId) {
            abort(403);
        }

        $cancellation = BookingCancellation::query()
            ->select([
                'id',
                'cancellation_number',
                'booking_id',
                'guest_user_id',
                'host_user_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'reason_key',
                'total_refund_amount',
                'host_payout_adjustment_amount',
                'calendar_release_status',
                'currency',
                'created_at',
            ])
            ->with([
                'guest:id,name',
                'room:id,title',
                'sleepingPlace:id,display_name,title',
                'refundLines:id,booking_cancellation_id,line_type,label_key,amount,currency,refundable,refund_amount,non_refundable_amount,reason_key,sort_order',
            ])
            ->where('host_user_id', $hostId)
            ->find($this->cancellationId);

        if (! $cancellation) {
            abort(403);
        }

        return $cancellation;
    }

    /**
     * @return Collection<int, BookingCancellation>
     */
    #[Computed]
    public function cancellations(): Collection
    {
        $hostId = Auth::id();

        if (! $hostId) {
            return collect();
        }

        return BookingCancellation::query()
            ->select([
                'id',
                'cancellation_number',
                'booking_id',
                'guest_user_id',
                'host_user_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'reason_key',
                'total_refund_amount',
                'calendar_release_status',
                'currency',
                'created_at',
            ])
            ->with(['guest:id,name', 'room:id,title', 'sleepingPlace:id,display_name,title'])
            ->where('host_user_id', $hostId)
            ->latest('id')
            ->limit(10)
            ->get();
    }

    private function authorizeHostContext(?int $hostUserId): void
    {
        $hostId = Auth::id();

        if (! $hostId || (int) $hostUserId !== (int) $hostId) {
            abort(403);
        }
    }
}
