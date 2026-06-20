<?php

namespace App\Services\Privacy;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\User;

class PublicProfileVisibility
{
    /**
     * @return array<string, mixed>
     */
    public function profileFor(User $subject, ?User $viewer = null): array
    {
        $subject->loadMissing(['profile', 'setting']);

        $preferences = PrivacyPreferences::normalize($subject->setting?->privacy_preferences_json);
        $guestPreferences = $preferences['guest'];
        $isSelf = $viewer instanceof User && (int) $viewer->id === (int) $subject->id;
        $displayName = $subject->profile?->display_name ?: $subject->name;

        return [
            'display_name' => $isSelf || $guestPreferences['show_display_name_publicly']
                ? $displayName
                : __('account.privacy.public_profile.hidden_name'),
            'full_name' => $this->canSeeFullName($subject, $viewer) ? $subject->name : null,
            'age' => ($isSelf || $guestPreferences['show_age']) ? $subject->age() : null,
            'age_range' => (! $guestPreferences['show_age'] && ($isSelf || $guestPreferences['show_age_range']))
                ? $this->ageRange($subject->age())
                : null,
            'city' => ($isSelf || $guestPreferences['show_city'])
                ? ($subject->profile?->city?->name ?: $subject->city)
                : null,
            'languages' => ($isSelf || $guestPreferences['show_languages'])
                ? $this->languages($subject)
                : [],
            'occupation' => ($isSelf || $guestPreferences['show_occupation'])
                ? ($subject->profile?->occupation ?: $subject->occupation)
                : null,
            'avatar_path' => ($isSelf || $guestPreferences['show_avatar'])
                ? ($subject->profile?->avatar_path ?: $subject->avatar)
                : null,
            'phone' => $this->canSeeGuestPhone($subject, $viewer) ? $this->phone($subject) : null,
            'show_reviews' => $isSelf || $guestPreferences['show_reviews'],
            'show_verification_status' => $isSelf || $guestPreferences['show_verification_status'],
            'verification' => [
                'email' => (bool) ($subject->profile?->email_verified_at ?: $subject->email_verified_at),
                'phone' => (bool) ($subject->profile?->phone_verified_at ?: $subject->phone_verified),
                'identity' => (bool) ($subject->profile?->identity_verified_at ?: $subject->identity_verified_at ?: $subject->identity_verified),
            ],
        ];
    }

    public function canSeeFullName(User $subject, ?User $viewer = null): bool
    {
        if (! $viewer instanceof User) {
            return false;
        }

        if ((int) $viewer->id === (int) $subject->id) {
            return true;
        }

        $subject->loadMissing('setting');

        if (! PrivacyPreferences::guest($subject->setting?->privacy_preferences_json, 'show_full_name_to_confirmed_hosts_only')) {
            return false;
        }

        return $this->hasConfirmedBookingBetween($subject, $viewer);
    }

    public function canSeeGuestPhone(User $guest, ?User $viewer = null): bool
    {
        if (! $viewer instanceof User) {
            return false;
        }

        if ((int) $viewer->id === (int) $guest->id) {
            return true;
        }

        $guest->loadMissing('setting');

        if (! PrivacyPreferences::guest($guest->setting?->privacy_preferences_json, 'show_phone_after_confirmed_booking')) {
            return false;
        }

        return $this->hasConfirmedBookingBetween($guest, $viewer);
    }

    public function hasConfirmedBookingBetween(User $guest, User $host): bool
    {
        return Booking::query()
            ->select(['id'])
            ->where('guest_user_id', $guest->id)
            ->where('host_user_id', $host->id)
            ->where(function ($query): void {
                $query
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->orWhereIn('status', $this->addressAccessStatusValues());
            })
            ->exists();
    }

    /**
     * @return list<string>
     */
    private function addressAccessStatusValues(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::LeavingSoon->value,
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    private function ageRange(?int $age): ?string
    {
        if (! $age) {
            return null;
        }

        return match (true) {
            $age < 25 => '18-24',
            $age < 35 => '25-34',
            $age < 45 => '35-44',
            $age < 55 => '45-54',
            default => '55+',
        };
    }

    /**
     * @return list<string>
     */
    private function languages(User $subject): array
    {
        $languages = $subject->profile?->languages_json ?: $subject->languages ?: [];

        return array_values(array_filter((array) $languages));
    }

    private function phone(User $subject): ?string
    {
        return $subject->profile?->phone ?: $subject->phone;
    }
}
