<?php

namespace App\Livewire\Booking;

use App\Models\Booking;
use App\Models\User;
use App\Services\CancellationService;
use App\Services\RefundCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CancelBooking extends Component
{
    #[Locked]
    public int $bookingId;

    public string $reason = '';

    public string $details = '';

    public bool $confirmed = false;

    public function mount(Booking $booking): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $booking->guest_user_id === (int) $user->id, 403);

        $this->bookingId = $booking->id;
    }

    public function submitCancellation(CancellationService $cancellations): mixed
    {
        $this->validate(
            [
                'reason' => ['required', 'string', Rule::in(array_keys($this->reasons()))],
                'details' => ['nullable', 'string', 'max:500'],
                'confirmed' => ['accepted'],
            ],
            [
                'confirmed.accepted' => __('booking.cancellation.validation.confirmed'),
            ],
            [
                'reason' => __('booking.cancellation.validation_attributes.reason'),
                'details' => __('booking.cancellation.validation_attributes.details'),
                'confirmed' => __('booking.cancellation.validation_attributes.confirmed'),
            ],
        );

        $booking = $this->booking();

        if (! $cancellations->cancelByGuest($booking, $this->reason, auth()->user(), $this->details ?: null)) {
            $this->addError('reason', __('booking.cancellation.errors.not_cancellable'));

            return null;
        }

        session()->flash('trip-status', __('notifications.flash.booking_cancelled'));

        return $this->redirectRoute('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $booking,
        ], navigate: true);
    }

    public function render(RefundCalculator $refunds): View
    {
        $booking = $this->booking();

        return view('livewire.booking.cancel-booking', [
            'booking' => $booking,
            'estimate' => $refunds->calculate($booking)->toArray(),
            'reasons' => $this->reasons(),
        ])->layout('layouts.app', [
            'title' => __('booking.cancellation.title'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function reasons(): array
    {
        return collect([
            'plans_changed',
            'found_another_place',
            'host_not_responding',
            'place_issue',
            'date_mistake',
            'payment_issue',
            'other',
        ])->mapWithKeys(fn (string $reason): array => [
            $reason => __('booking.cancellation.reasons.'.$reason),
        ])->all();
    }

    private function booking(): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'reference',
                'guest_user_id',
                'host_user_id',
                'sleeping_place_id',
                'property_id',
                'room_id',
                'status',
                'payment_status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'nights',
                'nights_count',
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
                'currency',
                'cancellation_policy',
                'free_cancel_before',
            ])
            ->with([
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->forGuest((int) auth()->id())
            ->findOrFail($this->bookingId);
    }
}
