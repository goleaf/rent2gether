<?php

namespace App\Livewire\Checkin;

use App\Actions\Bookings\GuestCheckIn;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Trips\Concerns\LoadsTripBookings;
use App\Models\Booking;
use App\Models\User;
use App\Support\Trips\TripBookingPresenter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CheckIn extends Component
{
    use LoadsTripBookings;

    #[Locked]
    public int $bookingId;

    public bool $propertyFound = false;

    public bool $keysReceived = false;

    public bool $codeReceived = false;

    public bool $roomSeen = false;

    public bool $sleepingPlaceShown = false;

    public bool $rulesSeen = false;

    public bool $everythingOk = false;

    public function mount(Booking $booking): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $booking->guest_user_id === (int) $user->id, 403);
        abort_unless($this->canCheckIn($booking), 403);

        $this->bookingId = $booking->id;

        $record = $booking->checkinRecord;

        if ($record) {
            $this->propertyFound = (bool) $record->property_found;
            $this->keysReceived = (bool) $record->keys_received;
            $this->codeReceived = (bool) $record->code_received;
            $this->roomSeen = (bool) $record->room_shown;
            $this->sleepingPlaceShown = (bool) $record->sleeping_place_shown;
            $this->rulesSeen = (bool) $record->rules_explained;
            $this->everythingOk = (bool) $record->everything_ok;
        }
    }

    public function submit(GuestCheckIn $checkIn): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $checkIn->handle($user, $this->booking(), [
            'property_found' => $this->propertyFound,
            'keys_received' => $this->keysReceived,
            'code_received' => $this->codeReceived,
            'room_seen' => $this->roomSeen,
            'sleeping_place_shown' => $this->sleepingPlaceShown,
            'rules_seen' => $this->rulesSeen,
            'everything_ok' => $this->everythingOk,
        ]);

        session()->flash('trip-status', __('notifications.flash.checkin_recorded'));

        $this->redirectRoute('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $this->bookingId,
        ], navigate: true);
    }

    public function render(TripBookingPresenter $presenter): View
    {
        $booking = $this->booking();

        return view('livewire.checkin.check-in', [
            'booking' => $booking,
            'trip' => $presenter->detail($booking),
        ])->layout('layouts.app', [
            'title' => __('booking.checkin.title'),
        ]);
    }

    private function booking(): Booking
    {
        return $this->tripBookingQuery()
            ->with(['checkinRecord:id,booking_id,property_found,keys_received,code_received,room_shown,sleeping_place_shown,rules_explained,everything_ok,guest_confirmed_at,host_confirmed_at,problem_reported,problem_description,status'])
            ->forGuest((int) auth()->id())
            ->findOrFail($this->bookingId);
    }

    private function canCheckIn(Booking $booking): bool
    {
        $status = $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
        $paymentStatus = $booking->payment_status instanceof PaymentStatus
            ? $booking->payment_status->value
            : (string) $booking->payment_status;

        return $paymentStatus === PaymentStatus::Paid->value
            && in_array($status, [
                BookingStatus::Confirmed->value,
                BookingStatus::Paid->value,
                BookingStatus::ReadyForCheckIn->value,
            ], true);
    }
}
