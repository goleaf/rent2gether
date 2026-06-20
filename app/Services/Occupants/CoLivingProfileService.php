<?php

namespace App\Services\Occupants;

use App\Data\Occupants\CoLivingProfileData;
use App\Models\Booking;
use App\Models\CoLivingProfile;
use App\Models\CoLivingVisibilitySetting;
use App\Models\User;

class CoLivingProfileService
{
    public function __construct(private readonly CoLivingPrivacyService $privacy) {}

    public function createDefaultForUser(User $user): CoLivingProfile
    {
        CoLivingVisibilitySetting::query()->firstOrCreate(['user_id' => $user->id]);

        return CoLivingProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'public_alias' => $this->defaultAlias($user),
                'languages_json' => $user->languages ?: [],
                'sleep_schedule' => $user->sleep_schedule,
                'smokes' => $user->is_smoker,
                'has_pet' => $user->has_pets,
                'prefers_quiet' => $user->prefers_quiet,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): CoLivingProfile
    {
        $profile = $this->createDefaultForUser($user);
        $profile->fill($data);
        $profile->save();

        return $profile;
    }

    public function getPublicProfile(User $viewer, User $occupant, ?Booking $booking = null): CoLivingProfileData
    {
        $profile = $occupant->coLivingProfile ?: $this->createDefaultForUser($occupant);
        $settings = $this->privacy->settingsFor($occupant);
        $afterBooking = $booking instanceof Booking
            && $this->privacy->canShowAfterConfirmedBooking($viewer, $occupant, $booking);

        return new CoLivingProfileData(
            alias: $settings->show_public_alias || $afterBooking ? $profile->public_alias : null,
            ageRange: $settings->show_age_range || $afterBooking ? $profile->age_range : null,
            languages: $settings->show_languages || $afterBooking ? $this->languages($profile->languages_json) : [],
            stayPurpose: $settings->show_stay_purpose || $afterBooking ? $profile->stay_purpose : null,
            guestType: $settings->show_guest_type || $afterBooking ? $profile->guest_type : null,
            sleepSchedule: $settings->show_sleep_schedule || $afterBooking ? $profile->sleep_schedule : null,
            wakeSchedule: $settings->show_wake_schedule || $afterBooking ? $profile->wake_schedule : null,
            homePresenceLevel: $settings->show_home_presence || $afterBooking ? $profile->home_presence_level : null,
            smokes: $settings->show_smoking_status || $afterBooking ? $profile->smokes : null,
            socialLevel: $settings->show_social_level || $afterBooking ? $profile->social_level : null,
            prefersQuiet: $settings->show_quiet_preference || $afterBooking ? $profile->prefers_quiet : null,
            roommateRating: $settings->show_roommate_rating || $afterBooking ? (float) $profile->roommate_rating_average : null,
            roommateReviewsCount: (int) $profile->roommate_reviews_count,
            badges: [],
        );
    }

    public function completeProfile(User $user): CoLivingProfile
    {
        $profile = $this->createDefaultForUser($user);
        $profile->forceFill(['profile_completed_at' => now()])->save();

        return $profile;
    }

    private function defaultAlias(User $user): ?string
    {
        return $user->name ? str($user->name)->before(' ')->toString() : null;
    }

    /**
     * @return list<string>
     */
    private function languages(mixed $languages): array
    {
        if (! is_array($languages)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $language): string => strtoupper((string) $language),
            $languages,
        )));
    }
}
