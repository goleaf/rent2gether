<?php

namespace App\Livewire\Booking;

use App\Actions\Payments\ConfirmDemoPayment;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\SleepingPlaceTranslation;
use App\Services\Bookings\BookingService;
use App\Services\Bookings\CancellationService;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingShow extends Component
{
    #[Locked]
    public int $bookingId;

    public string $cancelReason = '';

    public bool $showCancelModal = false;

    public function mount(Booking $booking): void
    {
        $this->bookingId = $booking->id;
    }

    #[Computed]
    public function booking(): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'bed_id',
                'sleeping_place_id',
                'room_id',
                'property_id',
                'guest_user_id',
                'host_user_id',
                'status',
                'payment_status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'nights',
                'nights_count',
                'guests_count',
                'subtotal',
                'subtotal_amount',
                'discount_amount',
                'cleaning_fee',
                'cleaning_fee_amount',
                'service_fee',
                'service_fee_amount',
                'total',
                'total_amount',
                'currency',
                'guest_message',
            ])
            ->with([
                'bed:id,room_id,title',
                'bed.room:id,property_id,title',
                'bed.room.property:id,title',
                'sleepingPlace:id,display_name',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
                'room:id,title',
                'property:id,title',
                'guest:id,name',
                'host:id,name',
                'priceLines:id,booking_id,line_type,label_key,amount,currency,sort_order',
            ])
            ->findOrFail($this->bookingId);
    }

    #[Computed]
    public function cancellationPreview(): ?array
    {
        if (! $this->booking->isCancellable()) {
            return null;
        }

        return app(CancellationService::class)->calculateRefund($this->booking);
    }

    #[Computed]
    public function daysUntilCheckIn(): int
    {
        return max(0, (int) now()->diffInDays($this->booking->check_in_date ?: $this->booking->check_in, false));
    }

    #[Computed]
    public function canCancel(): bool
    {
        return $this->booking->isCancellable();
    }

    #[Computed]
    public function canCheckIn(): bool
    {
        return in_array($this->booking->status, [
            BookingStatus::Confirmed,
            BookingStatus::Paid,
            BookingStatus::ReadyForCheckIn,
        ], true);
    }

    #[Computed]
    public function canCheckOut(): bool
    {
        return $this->booking->status?->isActive() ?? false;
    }

    #[Computed]
    public function canPay(): bool
    {
        $status = $this->booking->status instanceof BookingStatus
            ? $this->booking->status->value
            : (string) $this->booking->status;

        $paymentStatus = $this->booking->payment_status instanceof PaymentStatus
            ? $this->booking->payment_status->value
            : (string) $this->booking->payment_status;

        return in_array($status, ConfirmDemoPayment::payableBookingStatuses(), true)
            && in_array($paymentStatus, ConfirmDemoPayment::payablePaymentStatuses(), true);
    }

    public function cancel(): void
    {
        $success = app(CancellationService::class)->cancelByGuest($this->booking, $this->cancelReason ?: null);

        if ($success) {
            $this->showCancelModal = false;
            session()->flash('success', __('notifications.flash.booking_cancelled'));
            unset($this->booking, $this->cancellationPreview, $this->canCancel, $this->canCheckIn, $this->canCheckOut, $this->canPay);
        }
    }

    public function confirmCheckIn(): void
    {
        app(BookingService::class)->checkIn($this->booking);
        unset($this->booking, $this->cancellationPreview, $this->canCancel, $this->canCheckIn, $this->canCheckOut, $this->canPay);
    }

    public function confirmCheckOut(): void
    {
        app(BookingService::class)->checkOut($this->booking);
        unset($this->booking, $this->cancellationPreview, $this->canCancel, $this->canCheckIn, $this->canCheckOut, $this->canPay);
    }

    public function render(): View
    {
        $booking = $this->booking;

        return view('livewire.booking.booking-show', [
            'booking' => $booking,
            'placeTitle' => $this->placeTitle(),
            'roomTitle' => $booking->room?->title ?: $booking->bed?->room?->title ?: __('booking.room'),
            'propertyTitle' => $booking->property?->title ?: $booking->bed?->room?->property?->title ?: __('booking.property'),
            'cancellationPreview' => $this->cancellationPreview,
        ]);
    }

    public function money(float|int|string|null $amount, string $currency): string
    {
        return Number::currency((float) ($amount ?: 0), $currency, app()->getLocale());
    }

    private function placeTitle(): string
    {
        $place = $this->booking->sleepingPlace;

        if ($place) {
            $translation = app(LocalizedModelContentResolver::class)->resolve(
                $place->translations,
                app()->getLocale(),
                config('localization.fallback_locale'),
            );

            return (string) ($translation instanceof SleepingPlaceTranslation
                ? $translation->title
                : ($place->display_name ?: __('booking.bed')));
        }

        return (string) ($this->booking->bed?->title ?: __('booking.bed'));
    }
}
