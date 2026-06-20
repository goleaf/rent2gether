<?php

namespace App\Livewire\Booking;

use App\Actions\Payments\ConfirmDemoPayment;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\PropertyTranslation;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PaymentPage extends Component
{
    #[Locked]
    public int $bookingId;

    public function mount(Booking $booking): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $booking->guest_user_id === (int) $user->id, 403);

        $this->bookingId = $booking->id;
    }

    public function markAsPaid(): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        app(ConfirmDemoPayment::class)->handle($user, $this->booking());

        session()->flash('payment-status', __('notifications.flash.payment_confirmed'));
    }

    public function render(): View
    {
        $booking = $this->booking();

        return view('livewire.booking.payment-page', [
            'booking' => $booking,
            'lineItems' => $this->lineItems($booking),
            'canUseDemoDriver' => $this->canUseDemoDriver(),
            'canPay' => $this->canPay($booking),
            'accessDetails' => $this->accessDetails($booking),
            'placeTitle' => $this->placeTitle($booking),
        ])->layout('layouts.app', [
            'title' => __('booking.payment_page.title'),
        ]);
    }

    /**
     * @return Collection<int, array{label:string,amount:float,currency:string,refundable:bool}>
     */
    private function lineItems(Booking $booking): Collection
    {
        if ($booking->priceLines->isNotEmpty()) {
            return $booking->priceLines
                ->map(fn ($line): array => [
                    'label' => __($line->label_key),
                    'amount' => (float) $line->amount,
                    'currency' => $line->currency ?: $booking->currency,
                    'refundable' => (bool) $line->is_refundable,
                ]);
        }

        return collect([
            [
                'label' => __('booking.total'),
                'amount' => (float) ($booking->total_amount ?: $booking->total),
                'currency' => $booking->currency ?: 'EUR',
                'refundable' => false,
            ],
        ]);
    }

    private function canUseDemoDriver(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    private function canPay(Booking $booking): bool
    {
        $status = $this->statusValue($booking);
        $paymentStatus = $this->paymentStatusValue($booking);

        return in_array($status, ConfirmDemoPayment::payableBookingStatuses(), true)
            && in_array($paymentStatus, ConfirmDemoPayment::payablePaymentStatuses(), true);
    }

    private function placeTitle(Booking $booking): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $booking->sleepingPlace?->translations,
            app()->getLocale(),
            config('localization.fallback_locale'),
        );

        if ($translation instanceof SleepingPlaceTranslation && filled($translation->title)) {
            return $translation->title;
        }

        return $booking->sleepingPlace?->display_name ?: __('booking.payment_page.summary.unnamed_place');
    }

    /**
     * @return array{address:?string,instructions:?string}|null
     */
    private function accessDetails(Booking $booking): ?array
    {
        if (! in_array($this->statusValue($booking), [BookingStatus::Confirmed->value, BookingStatus::Paid->value], true)) {
            return null;
        }

        if ($this->paymentStatusValue($booking) !== PaymentStatus::Paid->value) {
            return null;
        }

        $property = $booking->property;

        if (! $property?->show_exact_address_after_payment) {
            return null;
        }

        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $property->translations,
            app()->getLocale(),
            config('localization.fallback_locale'),
        );

        $address = collect([
            $property->city,
            $property->district,
            $property->address_line_1,
            $property->house_number,
            $property->apartment_number,
        ])
            ->filter()
            ->implode(', ');

        return [
            'address' => $address !== '' ? $address : null,
            'instructions' => $booking->check_in_instructions
                ?: ($translation instanceof PropertyTranslation ? $translation->check_in_instructions : null),
        ];
    }

    private function booking(): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'reference',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'payment_method',
                'payment_paid_at',
                'payment_deadline_at',
                'check_in_date',
                'check_out_date',
                'nights_count',
                'calendar_days_count',
                'guests_count',
                'currency',
                'subtotal_amount',
                'discount_amount',
                'cleaning_fee_amount',
                'service_fee_amount',
                'deposit_amount',
                'total_amount',
                'refundable_amount',
                'non_refundable_amount',
                'cancellation_policy',
                'free_cancel_before',
                'check_in_instructions',
            ])
            ->with([
                'priceLines:id,booking_id,type,label_key,amount,currency,is_refundable',
                'paymentRecords:id,booking_id,provider,provider_reference,amount,currency,status,paid_at,created_at',
                'property:id,title,city,district,address_line_1,house_number,apartment_number,show_exact_address_after_payment',
                'property.translations:id,property_id,locale,title,check_in_instructions',
                'room:id,title',
                'sleepingPlace:id,display_name',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->findOrFail($this->bookingId);
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }

    private function paymentStatusValue(Booking $booking): string
    {
        return $booking->payment_status instanceof PaymentStatus
            ? $booking->payment_status->value
            : (string) $booking->payment_status;
    }
}
