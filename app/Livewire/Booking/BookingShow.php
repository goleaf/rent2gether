<?php

namespace App\Livewire\Booking;

use App\Actions\Payments\ConfirmDemoPayment;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\SleepingPlaceTranslation;
use App\Services\BookingService;
use App\Services\CancellationService;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingShow extends Component
{
    #[Locked]
    public Booking $booking;

    public string $cancelReason = '';

    public bool $showCancelModal = false;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load([
            'bed.room.property',
            'sleepingPlace.translations',
            'room',
            'property',
            'guest',
            'host',
            'priceLines',
        ]);
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
            $this->booking->refresh();
        }
    }

    public function confirmCheckIn(): void
    {
        app(BookingService::class)->checkIn($this->booking);
        $this->booking->refresh();
    }

    public function confirmCheckOut(): void
    {
        app(BookingService::class)->checkOut($this->booking);
        $this->booking->refresh();
    }

    public function render(): View
    {
        return view('livewire.booking.booking-show', [
            'placeTitle' => $this->placeTitle(),
            'roomTitle' => $this->booking->room?->title ?: $this->booking->bed?->room?->title ?: __('booking.room'),
            'propertyTitle' => $this->booking->property?->title ?: $this->booking->bed?->room?->property?->title ?: __('booking.property'),
        ]);
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
