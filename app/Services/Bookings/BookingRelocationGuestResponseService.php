<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationGuestResponse;
use App\Models\BookingRelocationOption;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingRelocationGuestResponseService
{
    public function __construct(
        private readonly BookingRelocationPrivacyService $privacy,
        private readonly BookingRelocationConsentService $consents,
        private readonly BookingRelocationEventService $events,
    ) {}

    public function accept(User $guest, BookingRelocation $relocation, ?string $message = null): BookingRelocationGuestResponse
    {
        if (! $this->privacy->canGuestConsent($guest, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        foreach ($relocation->consents()->where('user_id', $guest->id)->where('status', 'pending')->get() as $consent) {
            $this->consents->accept($guest, $consent, $message);
        }

        return $this->response($guest, $relocation, 'accept', $message);
    }

    public function reject(User $guest, BookingRelocation $relocation, string $message): BookingRelocationGuestResponse
    {
        if (! $this->privacy->canGuestConsent($guest, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $relocation->forceFill([
            'status' => 'cancelled_by_guest',
            'cancelled_at' => now(),
        ])->save();

        return $this->response($guest, $relocation, 'reject', $message);
    }

    public function selectOption(User $guest, BookingRelocationOption $option): BookingRelocationGuestResponse
    {
        $option->loadMissing('relocation');

        if (! $this->privacy->canGuestView($guest, $option->relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $option->relocation->options()->update(['guest_selected' => false, 'selected_at' => null]);
        $option->forceFill([
            'guest_selected' => true,
            'selected_at' => now(),
        ])->save();

        $option->relocation->forceFill([
            'new_property_id' => $option->property_id,
            'new_room_id' => $option->room_id,
            'new_sleeping_place_id' => $option->sleeping_place_id,
        ])->save();

        $this->events->record($option->relocation->refresh(), 'option_selected', ['user_id' => $guest->id]);

        return $this->response($guest, $option->relocation, 'select_option', selectedOptionId: $option->id, acceptedPlaceId: $option->sleeping_place_id);
    }

    public function acceptPriceDifference(User $guest, BookingRelocation $relocation): BookingRelocationGuestResponse
    {
        return $this->response($guest, $relocation, 'accept_price_difference');
    }

    public function rejectPriceDifference(User $guest, BookingRelocation $relocation): BookingRelocationGuestResponse
    {
        return $this->response($guest, $relocation, 'reject_price_difference');
    }

    public function cancelRequest(User $guest, BookingRelocation $relocation): BookingRelocationGuestResponse
    {
        $relocation->forceFill([
            'status' => 'cancelled_by_guest',
            'cancelled_at' => now(),
        ])->save();

        return $this->response($guest, $relocation, 'cancel_request');
    }

    private function response(
        User $guest,
        BookingRelocation $relocation,
        string $type,
        ?string $message = null,
        ?int $selectedOptionId = null,
        ?int $acceptedPlaceId = null,
    ): BookingRelocationGuestResponse {
        return BookingRelocationGuestResponse::query()->create([
            'booking_relocation_id' => $relocation->id,
            'guest_user_id' => $guest->id,
            'response_type' => $type,
            'message' => $message,
            'selected_option_id' => $selectedOptionId,
            'accepted_sleeping_place_id' => $acceptedPlaceId,
            'accepted_relocation_date' => $relocation->relocation_date,
            'accepted_relocation_time' => $relocation->relocation_time,
        ]);
    }
}
