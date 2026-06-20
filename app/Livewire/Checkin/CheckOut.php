<?php

namespace App\Livewire\Checkin;

use App\Actions\Bookings\GuestCheckOut;
use App\Enums\BookingStatus;
use App\Livewire\Trips\Concerns\LoadsTripBookings;
use App\Models\Booking;
use App\Models\User;
use App\Support\Trips\TripBookingPresenter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CheckOut extends Component
{
    use LoadsTripBookings;

    #[Locked]
    public int $bookingId;

    public bool $keysReturned = false;

    public bool $belongingsRemoved = false;

    public bool $lockerEmptied = false;

    public bool $placeClean = false;

    public function mount(Booking $booking): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $booking->guest_user_id === (int) $user->id, 403);
        abort_unless($this->canCheckOut($booking), 403);

        $this->bookingId = $booking->id;

        $record = $booking->checkoutRecord;

        if ($record) {
            $this->keysReturned = (bool) $record->keys_returned;
            $this->belongingsRemoved = (bool) $record->belongings_removed;
            $this->lockerEmptied = (bool) $record->locker_emptied;
            $this->placeClean = (bool) $record->place_clean;
        }
    }

    public function submit(GuestCheckOut $checkOut): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $checkOut->handle($user, $this->booking(), [
            'keys_returned' => $this->keysReturned,
            'belongings_removed' => $this->belongingsRemoved,
            'locker_emptied' => $this->lockerEmptied,
            'place_clean' => $this->placeClean,
        ]);

        session()->flash('trip-status', __('notifications.flash.checkout_recorded'));

        $this->redirectRoute('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $this->bookingId,
        ], navigate: true);
    }

    public function render(TripBookingPresenter $presenter): View
    {
        $booking = $this->booking();

        return view('livewire.checkin.check-out', [
            'booking' => $booking,
            'trip' => $presenter->detail($booking),
        ])->layout('layouts.app', [
            'title' => __('booking.checkout.title'),
        ]);
    }

    private function booking(): Booking
    {
        return $this->tripBookingQuery()
            ->with(['checkoutRecord:id,booking_id,keys_returned,belongings_removed,locker_emptied,place_clean,guest_confirmed_checkout_at,host_confirmed_checkout_at,status'])
            ->forGuest((int) auth()->id())
            ->findOrFail($this->bookingId);
    }

    private function canCheckOut(Booking $booking): bool
    {
        $status = $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;

        return in_array($status, [
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::LeavingSoon->value,
        ], true);
    }
}
