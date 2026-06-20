<?php

namespace App\Services\Users;

use App\Models\BookingGuestIntake;
use App\Models\User;
use App\Services\BookingGuestIntake\BookingGuestIntakeService;
use Illuminate\Support\Arr;

class UserProfileVisibilityService
{
    public function __construct(
        private readonly UserPrivacyService $privacy,
        private readonly UserVerificationService $verifications,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildPublicGuestProfile(User $viewer, User $guest): array
    {
        unset($viewer);

        $settings = $this->privacy->getOrCreate($guest);
        $guest->loadMissing(['profile', 'userLanguages', 'activitySummary']);
        $profile = $guest->profile;
        $summary = $guest->activitySummary;

        return [
            'public_name' => $settings->show_real_name
                ? ($profile?->display_name ?: $guest->name)
                : ($profile?->public_name ?: $profile?->display_name ?: __('profiles.public.guest_fallback_name')),
            'avatar_path' => $settings->show_avatar ? ($profile?->avatar_path ?: $guest->avatar_path) : null,
            'age_range' => $settings->show_age_range ? $profile?->age_range_public : null,
            'city' => $settings->show_city ? $profile?->public_city_name : null,
            'languages' => $settings->show_languages ? $guest->userLanguages->pluck('language_code')->values()->all() : [],
            'about' => $profile?->about,
            'rating' => $settings->show_rating ? $summary?->average_guest_rating : null,
            'completed_stays_count' => $settings->show_completed_stays_count ? (int) ($summary?->completed_stays_as_guest ?? 0) : null,
            'reviews_count' => $settings->show_reviews_count ? (int) ($summary?->reviews_received_count ?? 0) : null,
            'identity_verified' => $settings->show_identity_verified_badge && $this->verifications->getVerificationStatus($guest, 'identity') === 'verified',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildHostViewOfGuest(User $host, User $guest, mixed $request = null): array
    {
        unset($host);

        $data = $this->buildPublicGuestProfile($guest, $guest);
        $guest->loadMissing(['activitySummary']);

        $data['verification'] = [
            'phone' => $this->verifications->getVerificationStatus($guest, 'phone'),
            'email' => $this->verifications->getVerificationStatus($guest, 'email'),
            'identity' => $this->verifications->getVerificationStatus($guest, 'identity'),
        ];
        $data['activity'] = [
            'completed_stays_as_guest' => (int) ($guest->activitySummary?->completed_stays_as_guest ?? 0),
            'reviews_received_count' => (int) ($guest->activitySummary?->reviews_received_count ?? 0),
            'average_guest_rating' => $guest->activitySummary?->average_guest_rating,
            'confirmed_complaints_count' => (int) ($guest->activitySummary?->confirmed_complaints_count ?? 0),
        ];

        if ($request instanceof BookingGuestIntake) {
            $data['booking_intake'] = app(BookingGuestIntakeService::class)->buildHostSummary($request);
        }

        return $this->filterSensitiveFields($guest, $guest, $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPublicHostProfile(User $viewer, User $host): array
    {
        unset($viewer);

        $host->loadMissing(['hostProfile', 'userLanguages', 'activitySummary']);
        $profile = $host->hostProfile;

        return [
            'name' => $profile?->host_display_name ?: $profile?->display_name ?: $host->name,
            'avatar_path' => $profile?->avatar_path ?: $host->avatar_path,
            'about' => $profile?->about_host ?: $profile?->about,
            'languages' => $host->userLanguages->pluck('language_code')->values()->all(),
            'rating' => $host->activitySummary?->average_host_rating ?: $profile?->rating_average,
            'reviews_count' => $host->activitySummary?->reviews_received_count ?: $profile?->reviews_count,
            'response_time_minutes' => $profile?->response_time_minutes,
            'response_rate' => $profile?->response_rate,
            'verified_host' => (bool) ($profile?->verified_host || $profile?->verified_at),
            'successful_check_ins_count' => (int) ($profile?->successful_check_ins_count ?? 0),
            'hosting_since' => $profile?->hosting_since?->toDateString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function filterSensitiveFields(User $viewer, User $target, array $data): array
    {
        unset($viewer, $target);

        return Arr::except($data, [
            'phone',
            'email',
            'date_of_birth',
            'birth_date',
            'documents',
            'document_files',
            'user_documents',
            'verification_metadata',
            'metadata_json',
            'file_path',
            'private_notes',
            'emergency_contact_phone',
            'emergency_contact_name',
        ]);
    }
}
