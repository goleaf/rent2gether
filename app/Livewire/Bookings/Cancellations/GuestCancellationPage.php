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
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GuestCancellationPage extends Component
{
    #[Locked]
    public ?int $bookingId = null;

    #[Locked]
    public ?int $previewId = null;

    #[Locked]
    public ?int $cancellationId = null;

    public string $variant = 'page';

    public string $reasonKey = 'changed_plans';

    public string $cancellationType = 'guest_fault';

    public ?string $comment = null;

    public function mount(?Booking $booking = null, ?BookingCancellationPreview $preview = null, ?BookingCancellation $cancellation = null): void
    {
        if ($booking instanceof Booking) {
            $this->authorizeGuestContext($booking->guest_user_id);
        }

        if ($preview instanceof BookingCancellationPreview) {
            $this->authorizeGuestContext($preview->guest_user_id);
        }

        if ($cancellation instanceof BookingCancellation) {
            $this->authorizeGuestContext($cancellation->guest_user_id);
        }

        $this->bookingId = $booking?->id ?? $preview?->booking_id ?? $cancellation?->booking_id;
        $this->previewId = $preview?->id;
        $this->cancellationId = $cancellation?->id;
    }

    public function createPreview(BookingCancellationPreviewService $previews): void
    {
        $booking = $this->booking;

        if (! $booking || ! Auth::user()) {
            return;
        }

        $preview = $previews->createPreview(Auth::user(), $booking, [
            'cancellation_type' => $this->cancellationType,
            'reason_key' => $this->reasonKey,
            'comment' => $this->comment,
        ]);

        $this->previewId = $preview->id;
        unset($this->preview);
    }

    public function confirmCancellation(BookingCancellationService $cancellations): void
    {
        $preview = $this->preview;

        if (! $preview || ! Auth::user()) {
            return;
        }

        $cancellation = $cancellations->confirmCancellation(Auth::user(), $preview);
        $this->cancellationId = $cancellation->id;
        unset($this->preview, $this->cancellation, $this->cancellations);
    }

    public function render(): View
    {
        return view('livewire.bookings.cancellations.card', [
            'booking' => $this->booking,
            'preview' => $this->preview,
            'cancellation' => $this->cancellation,
            'cancellations' => $this->cancellations,
            'variant' => $this->variant,
            'reasonOptions' => $this->reasonOptions(),
        ]);
    }

    #[Computed]
    public function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        $guestId = Auth::id();

        if (! $guestId) {
            abort(403);
        }

        $booking = Booking::query()
            ->select(['id', 'guest_user_id', 'host_user_id', 'room_id', 'sleeping_place_id', 'check_in_date', 'check_out_date', 'currency', 'total_payable', 'status'])
            ->with(['room:id,title', 'sleepingPlace:id,display_name,title'])
            ->where('guest_user_id', $guestId)
            ->find($this->bookingId);

        if (! $booking) {
            abort(403);
        }

        return $booking;
    }

    #[Computed]
    public function preview(): ?BookingCancellationPreview
    {
        if (! $this->previewId) {
            return null;
        }

        $guestId = Auth::id();

        if (! $guestId) {
            abort(403);
        }

        $preview = BookingCancellationPreview::query()
            ->select([
                'id',
                'preview_number',
                'booking_id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'requested_by_user_id',
                'requested_by_type',
                'cancellation_type',
                'reason_key',
                'comment',
                'check_in_date',
                'check_out_date',
                'hours_before_check_in',
                'nights_before_check_in',
                'nights_used',
                'nights_unused',
                'accommodation_amount',
                'cleaning_fee_amount',
                'service_fee_amount',
                'deposit_amount',
                'tax_amount',
                'city_fee_amount',
                'accommodation_refund_amount',
                'cleaning_fee_refund_amount',
                'service_fee_refund_amount',
                'deposit_refund_amount',
                'tax_refund_amount',
                'city_fee_refund_amount',
                'penalty_amount',
                'host_payout_adjustment_amount',
                'total_refund_amount',
                'total_non_refundable_amount',
                'currency',
                'expires_at',
                'status',
            ])
            ->with([
                'booking:id,sleeping_place_id,check_in,check_in_date,check_in_time,total,total_amount,total_payable,payment_status,free_cancel_before',
                'sleepingPlace:id,display_name,title',
            ])
            ->where('guest_user_id', $guestId)
            ->find($this->previewId);

        if (! $preview) {
            abort(403);
        }

        return $preview;
    }

    #[Computed]
    public function cancellation(): ?BookingCancellation
    {
        if (! $this->cancellationId) {
            return null;
        }

        $guestId = Auth::id();

        if (! $guestId) {
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
                'refund_status',
                'total_refund_amount',
                'calendar_release_status',
                'currency',
                'created_at',
            ])
            ->with([
                'refundLines:id,booking_cancellation_id,line_type,label_key,amount,currency,refundable,refund_amount,non_refundable_amount,reason_key,sort_order',
                'sleepingPlace:id,display_name,title',
            ])
            ->where('guest_user_id', $guestId)
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
        if (! $this->bookingId) {
            return collect();
        }

        $guestId = Auth::id();

        if (! $guestId) {
            return collect();
        }

        return BookingCancellation::query()
            ->select([
                'id',
                'cancellation_number',
                'booking_id',
                'guest_user_id',
                'status',
                'reason_key',
                'created_at',
            ])
            ->where('booking_id', $this->bookingId)
            ->where('guest_user_id', $guestId)
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

    private function authorizeGuestContext(?int $guestUserId): void
    {
        $guestId = Auth::id();

        if (! $guestId || (int) $guestUserId !== (int) $guestId) {
            abort(403);
        }
    }
}
