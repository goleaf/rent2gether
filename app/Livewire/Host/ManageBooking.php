<?php

namespace App\Livewire\Host;

use App\Actions\Bookings\HostConfirmCheckIn;
use App\Actions\Bookings\HostConfirmCheckOut;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use App\Services\CancellationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageBooking extends Component
{
    #[Locked]
    public int $bookingId;

    public string $rejectReason = '';

    public string $cancelReason = '';

    public string $hostReply = '';

    public bool $showRejectModal = false;

    public bool $showCancelModal = false;

    public function mount(Booking $booking): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $booking->host_user_id === (int) $user->id, 403);

        $this->bookingId = $booking->id;
    }

    public function approve(): void
    {
        app(BookingService::class)->confirmByHost($this->booking());
        session()->flash('success', __('notifications.flash.booking_approved'));
    }

    public function reject(): void
    {
        app(BookingService::class)->rejectByHost($this->booking(), $this->rejectReason ?: null);
        $this->showRejectModal = false;
        session()->flash('success', __('notifications.flash.booking_rejected'));
    }

    public function cancel(): void
    {
        app(CancellationService::class)->cancelByHost($this->booking(), $this->cancelReason ?: null);
        $this->showCancelModal = false;
        session()->flash('success', __('notifications.flash.booking_refunded'));
    }

    public function confirmCheckIn(HostConfirmCheckIn $confirmCheckIn): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $confirmCheckIn->handle($user, $this->booking());

        session()->flash('success', __('notifications.flash.host_checkin_confirmed'));
    }

    public function confirmCheckOut(HostConfirmCheckOut $confirmCheckOut): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $confirmCheckOut->handle($user, $this->booking(), [
            'place_inspected' => true,
            'no_damage' => true,
            'damage_found' => false,
            'deposit_action' => 'return',
        ]);

        session()->flash('success', __('notifications.flash.host_checkout_confirmed'));
    }

    public function sendReply(): void
    {
        $this->validate(['hostReply' => ['required', 'string', 'max:2000']]);
        $this->booking()->update(['host_reply' => $this->hostReply]);
        $this->hostReply = '';
    }

    public function render(): View
    {
        $booking = $this->booking();

        return view('livewire.host.manage-booking', [
            'booking' => $booking,
            'placeTitle' => $this->placeTitle($booking),
        ]);
    }

    private function booking(): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'reference',
                'guest_id',
                'guest_user_id',
                'host_id',
                'host_user_id',
                'bed_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'check_out_time',
                'arrival_time',
                'nights',
                'nights_count',
                'currency',
                'subtotal',
                'subtotal_amount',
                'discount_amount',
                'cleaning_fee',
                'cleaning_fee_amount',
                'deposit',
                'deposit_amount',
                'service_fee',
                'service_fee_amount',
                'total',
                'total_amount',
                'cancellation_policy',
                'free_cancel_before',
                'cancel_reason',
                'cancellation_reason',
                'guest_message',
                'host_reply',
                'host_response',
                'guest_review_left',
                'host_review_left',
                'review_deadline_at',
                'guest_checked_in_at',
                'guest_checked_out_at',
                'host_confirmed_checkin_at',
                'host_confirmed_checkout_at',
                'checked_in_at',
                'checked_out_at',
                'deposit_released_at',
            ])
            ->with([
                'guest:id,name,email,phone,rating_as_guest,completed_stays_count,complaints_count',
                'guest.profile:id,user_id,phone,display_name',
                'bed:id,room_id,title',
                'room:id,property_id,title,room_number',
                'property:id,title,city,district',
                'sleepingPlace:id,room_id,property_id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
                'checkinRecord:id,booking_id,guest_confirmed,host_confirmed,guest_confirmed_at,host_confirmed_at,problem_reported,problem_description,status',
                'checkoutRecord:id,booking_id,guest_confirmed,host_confirmed,guest_confirmed_checkout_at,host_confirmed_checkout_at,damage_found,damage_description,deposit_action,status',
                'depositRecords:id,booking_id,amount,currency,status,held_at,released_at,withheld_amount,withhold_reason',
            ])
            ->forHost((int) auth()->id())
            ->findOrFail($this->bookingId);
    }

    private function placeTitle(Booking $booking): string
    {
        $translation = $booking->sleepingPlace?->translations
            ?->firstWhere('locale', app()->getLocale());

        return $translation?->title
            ?: $booking->sleepingPlace?->display_name
            ?: $booking->sleepingPlace?->place_number
            ?: $booking->bed?->title
            ?: __('booking.payment_page.summary.unnamed_place');
    }
}
