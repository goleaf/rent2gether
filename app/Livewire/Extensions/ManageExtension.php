<?php

namespace App\Livewire\Extensions;

use App\Enums\BookingExtensionStatus;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Services\Bookings\ExtensionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageExtension extends Component
{
    #[Locked]
    public int $bookingId;

    #[Locked]
    public int $extensionId;

    public string $hostResponse = '';

    public string $declineReason = '';

    public function mount(Booking $booking, BookingExtension $extension): void
    {
        abort_unless((int) $booking->host_user_id === (int) auth()->id(), 403);
        abort_unless((int) $extension->booking_id === (int) $booking->id, 404);

        $this->bookingId = $booking->id;
        $this->extensionId = $extension->id;
        $this->hostResponse = $extension->host_response ?: $extension->host_reply ?: '';
        $this->declineReason = $extension->reject_reason ?: '';
    }

    public function approve(): void
    {
        $this->validate([
            'hostResponse' => ['nullable', 'string', 'max:1000'],
        ], attributes: $this->validationAttributes());

        try {
            app(ExtensionService::class)->approve(auth()->user(), $this->extension(), $this->blankToNull($this->hostResponse));
        } catch (ValidationException $exception) {
            $this->copyValidationErrors($exception);

            return;
        } catch (AuthorizationException) {
            abort(403);
        }

        session()->flash('success', __('notifications.flash.extension_approved'));
    }

    public function reject(): void
    {
        $this->validate([
            'declineReason' => ['required', 'string', 'max:120'],
            'hostResponse' => ['nullable', 'string', 'max:1000'],
        ], attributes: $this->validationAttributes());

        try {
            app(ExtensionService::class)->reject(
                auth()->user(),
                $this->extension(),
                $this->declineReason,
                $this->blankToNull($this->hostResponse),
            );
        } catch (ValidationException $exception) {
            $this->copyValidationErrors($exception);

            return;
        } catch (AuthorizationException) {
            abort(403);
        }

        session()->flash('success', __('notifications.flash.extension_rejected'));
    }

    public function render(): View
    {
        $extension = $this->extension();

        return view('livewire.extensions.manage-extension', [
            'extension' => $extension,
            'booking' => $extension->booking,
            'placeTitle' => $this->placeTitle($extension),
            'statusValue' => $this->statusValue($extension),
            'declineReasons' => $this->declineReasons(),
        ])->layout('layouts.app', [
            'title' => __('booking.extension.manage_title'),
        ]);
    }

    private function placeTitle(BookingExtension $extension): string
    {
        $place = $extension->booking?->sleepingPlace;

        return $place?->translations?->firstWhere('locale', app()->getLocale())?->title
            ?: $place?->translations?->firstWhere('locale', config('localization.fallback_locale', 'en'))?->title
            ?: $place?->display_name
            ?: __('booking.bed');
    }

    private function statusValue(BookingExtension $extension): string
    {
        return $extension->status instanceof BookingExtensionStatus
            ? $extension->status->value
            : (string) $extension->status;
    }

    private function extension(): BookingExtension
    {
        return BookingExtension::query()
            ->with([
                'booking.guest:id,name',
                'booking.sleepingPlace:id,display_name',
                'booking.sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->where('booking_id', $this->bookingId)
            ->findOrFail($this->extensionId);
    }

    /**
     * @return array<string, string>
     */
    private function declineReasons(): array
    {
        return [
            'calendar_changed' => __('booking.extension.decline_reasons.calendar_changed'),
            'not_possible_now' => __('booking.extension.decline_reasons.not_possible_now'),
            'host_schedule' => __('booking.extension.decline_reasons.host_schedule'),
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
