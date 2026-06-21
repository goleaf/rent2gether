<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationConsent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingRelocationConsentService
{
    public function __construct(
        private readonly BookingRelocationEventService $events,
        private readonly BookingRelocationPrivacyService $privacy,
    ) {}

    public function requestGuestConsent(BookingRelocation $relocation): BookingRelocationConsent
    {
        return BookingRelocationConsent::query()->firstOrCreate([
            'booking_relocation_id' => $relocation->id,
            'user_id' => $relocation->guest_user_id,
            'consent_type' => 'guest_accepts_new_place',
        ], [
            'status' => 'pending',
        ]);
    }

    public function requestHostConsent(BookingRelocation $relocation): BookingRelocationConsent
    {
        return BookingRelocationConsent::query()->firstOrCreate([
            'booking_relocation_id' => $relocation->id,
            'user_id' => $relocation->host_user_id,
            'consent_type' => 'host_accepts_relocation',
        ], [
            'status' => 'pending',
        ]);
    }

    public function accept(User $user, BookingRelocationConsent $consent, ?string $message = null): BookingRelocationConsent
    {
        $consent->loadMissing('relocation');
        $relocation = $consent->relocation;

        if (! $this->privacy->canGuestConsent($user, $relocation) && ! $this->privacy->canHostConsent($user, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        if ((int) $consent->user_id !== (int) $user->id) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $consent->forceFill([
            'status' => 'accepted',
            'message' => $message,
            'responded_at' => now(),
        ])->save();

        if ((int) $user->id === (int) $relocation->guest_user_id) {
            $relocation->forceFill(['guest_consented_at' => now()])->save();
            $this->events->record($relocation->refresh(), 'guest_consented', ['user_id' => $user->id]);
        }

        if ((int) $user->id === (int) $relocation->host_user_id) {
            $relocation->forceFill(['host_consented_at' => now()])->save();
            $this->events->record($relocation->refresh(), 'host_consented', ['user_id' => $user->id]);
        }

        return $consent->refresh();
    }

    public function reject(User $user, BookingRelocationConsent $consent, ?string $message = null): BookingRelocationConsent
    {
        $consent->loadMissing('relocation');

        if ((int) $consent->user_id !== (int) $user->id) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $consent->forceFill([
            'status' => 'rejected',
            'message' => $message,
            'responded_at' => now(),
        ])->save();

        return $consent->refresh();
    }

    public function allRequiredConsentsGiven(BookingRelocation $relocation): bool
    {
        if ($relocation->requires_guest_consent && ! $relocation->guest_consented_at) {
            return false;
        }

        if ($relocation->requires_host_consent && ! $relocation->host_consented_at) {
            return false;
        }

        return ! $relocation->consents()
            ->where('status', 'pending')
            ->exists();
    }
}
