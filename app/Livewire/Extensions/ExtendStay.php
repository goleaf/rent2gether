<?php

namespace App\Livewire\Extensions;

use App\Enums\BookingExtensionStatus;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Services\ExtensionService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ExtendStay extends Component
{
    #[Locked]
    public int $bookingId;

    public string $requestedNewCheckout = '';

    public string $guestMessage = '';

    /** @var array<string, mixed>|null */
    public ?array $preview = null;

    public ?int $extensionId = null;

    public function mount(Booking $booking): void
    {
        abort_unless((int) $booking->guest_user_id === (int) auth()->id(), 403);

        $this->bookingId = $booking->id;

        $extension = $this->activeExtension();

        if ($extension) {
            $this->extensionId = $extension->id;
            $this->requestedNewCheckout = $extension->requested_new_checkout_date?->toDateString()
                ?: $extension->new_check_out?->toDateString()
                ?: '';
            $this->guestMessage = $extension->guest_message ?: '';
            $this->preview = $this->previewFromExtension($extension);

            return;
        }

        $currentCheckout = CarbonImmutable::parse($booking->check_out_date ?: $booking->check_out)->startOfDay();
        $this->requestedNewCheckout = $currentCheckout->addDay()->toDateString();
        $this->refreshPreview();
    }

    public function updatedRequestedNewCheckout(): void
    {
        $this->refreshPreview();
    }

    public function refreshPreview(): void
    {
        $this->resetErrorBag('requestedNewCheckout');
        $this->preview = null;

        if ($this->requestedNewCheckout === '') {
            return;
        }

        try {
            $this->preview = app(ExtensionService::class)->preview(
                auth()->user(),
                $this->booking(),
                $this->requestedNewCheckout,
            );
        } catch (ValidationException $exception) {
            $this->copyValidationErrors($exception);
        } catch (AuthorizationException) {
            abort(403);
        }
    }

    public function submit(): void
    {
        $this->validate($this->rules(), attributes: $this->validationAttributes());

        try {
            $extension = app(ExtensionService::class)->request(
                auth()->user(),
                $this->booking(),
                $this->requestedNewCheckout,
                $this->blankToNull($this->guestMessage),
            );
        } catch (ValidationException $exception) {
            $this->copyValidationErrors($exception);

            return;
        } catch (AuthorizationException) {
            abort(403);
        }

        $this->extensionId = $extension->id;
        $this->preview = $this->previewFromExtension($extension);

        session()->flash('success', $extension->status === BookingExtensionStatus::AwaitingPayment
            ? __('notifications.flash.extension_ready_for_payment')
            : __('notifications.flash.extension_requested'));
    }

    public function payExtension(): void
    {
        $extension = $this->activeExtension();

        if (! $extension) {
            $this->addError('extension', __('booking.extension.errors.status_changed'));

            return;
        }

        try {
            $extension = app(ExtensionService::class)->markPaid(auth()->user(), $extension);
        } catch (ValidationException $exception) {
            $this->copyValidationErrors($exception);

            return;
        } catch (AuthorizationException) {
            abort(403);
        }

        $this->extensionId = $extension->id;
        $this->preview = $this->previewFromExtension($extension);

        session()->flash('success', __('notifications.flash.extension_payment_recorded'));

        $this->redirect(route('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $this->bookingId,
        ]), navigate: true);
    }

    public function cancelExtension(): void
    {
        $extension = $this->activeExtension();

        if (! $extension) {
            $this->addError('extension', __('booking.extension.errors.status_changed'));

            return;
        }

        try {
            app(ExtensionService::class)->cancel(auth()->user(), $extension);
        } catch (ValidationException $exception) {
            $this->copyValidationErrors($exception);

            return;
        } catch (AuthorizationException) {
            abort(403);
        }

        $this->extensionId = null;
        $this->preview = null;
        session()->flash('success', __('notifications.flash.extension_cancelled'));
        $this->refreshPreview();
    }

    public function render(): View
    {
        return view('livewire.extensions.extend-stay', [
            'booking' => $this->booking(),
            'extension' => $this->activeExtension(),
            'canUseDemoPayment' => app()->environment(['local', 'testing']),
        ])->layout('layouts.app', [
            'title' => __('booking.extension.title'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'requestedNewCheckout' => ['required', 'date'],
            'guestMessage' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('booking.extension.validation_attributes');

        return is_array($attributes) ? $attributes : [];
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
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'guests_count',
                'nights',
                'nights_count',
                'calendar_days_count',
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
                'refundable_amount',
                'non_refundable_amount',
                'currency',
            ])
            ->with([
                'guest:id,name',
                'guest.setting:id,user_id,locale',
                'host:id,name',
                'host.setting:id,user_id,locale',
                'sleepingPlace:id,room_id,property_id,display_name,status,min_nights,max_nights,max_guests,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency,instant_booking_enabled,requires_host_approval,extensions_allowed',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
                'sleepingPlace.room:id,property_id,status',
                'sleepingPlace.property:id,status',
            ])
            ->findOrFail($this->bookingId);
    }

    private function activeExtension(): ?BookingExtension
    {
        $query = BookingExtension::query()
            ->where('booking_id', $this->bookingId ?? 0)
            ->whereIn('status', [
                BookingExtensionStatus::AwaitingHostApproval->value,
                BookingExtensionStatus::AwaitingPayment->value,
                BookingExtensionStatus::Approved->value,
            ])
            ->latest('id');

        if ($this->extensionId) {
            $query->whereKey($this->extensionId);
        }

        return $query->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function previewFromExtension(BookingExtension $extension): array
    {
        return [
            'current_checkout_date' => $extension->current_checkout_date?->toDateString(),
            'requested_new_checkout_date' => $extension->requested_new_checkout_date?->toDateString(),
            'additional_nights' => $extension->additional_nights,
            'additional_amount' => (float) $extension->additional_amount,
            'discount_amount' => (float) $extension->discount_amount,
            'service_fee_amount' => round((float) $extension->total_extra - max(0.0, (float) $extension->additional_amount - (float) $extension->discount_amount), 2),
            'total_extra' => (float) $extension->total_extra,
            'new_total' => (float) $extension->new_total,
            'currency' => $extension->booking?->currency ?: 'EUR',
            'payment_required' => (bool) $extension->payment_required,
            'requires_host_approval' => (bool) $extension->requires_host_approval,
            'next_status' => $extension->status instanceof BookingExtensionStatus
                ? $extension->status->value
                : (string) $extension->status,
        ];
    }

    private function copyValidationErrors(ValidationException $exception): void
    {
        foreach ($exception->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $this->addError($field, $message);
            }
        }
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
