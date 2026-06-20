<?php

namespace App\Livewire\Profile;

use App\Services\Occupants\CoLivingProfileService;
use App\Services\Occupants\RoomOccupantSnapshotService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CoLivingProfileForm extends Component
{
    public string $publicAlias = '';

    public string $ageRange = '';

    public string $genderForRoomPolicy = '';

    public string $languages = '';

    public string $stayPurpose = '';

    public string $guestType = '';

    public bool $tourist = false;

    public bool $student = false;

    public bool $working = false;

    public bool $remoteWorker = false;

    public bool $longTermGuest = false;

    public bool $shortTermGuest = false;

    public string $sleepSchedule = '';

    public string $wakeSchedule = '';

    public string $homePresenceLevel = '';

    public bool $smokes = false;

    public string $smokingLocation = '';

    public bool $hasPet = false;

    public string $socialLevel = '';

    public bool $prefersQuiet = false;

    public string $cleanlinessLevel = '';

    public bool $participatesInCleaning = false;

    public bool $respectsPersonalSpace = true;

    public function mount(CoLivingProfileService $profiles): void
    {
        $profile = $profiles->createDefaultForUser(auth()->user());

        $this->publicAlias = $profile->public_alias ?? '';
        $this->ageRange = $profile->age_range ?? '';
        $this->genderForRoomPolicy = $profile->gender_for_room_policy ?? '';
        $this->languages = implode(', ', $profile->languages_json ?: []);
        $this->stayPurpose = $profile->stay_purpose ?? '';
        $this->guestType = $profile->guest_type ?? '';
        $this->tourist = (bool) $profile->tourist;
        $this->student = (bool) $profile->student;
        $this->working = (bool) $profile->working;
        $this->remoteWorker = (bool) $profile->remote_worker;
        $this->longTermGuest = (bool) $profile->long_term_guest;
        $this->shortTermGuest = (bool) $profile->short_term_guest;
        $this->sleepSchedule = $profile->sleep_schedule ?? '';
        $this->wakeSchedule = $profile->wake_schedule ?? '';
        $this->homePresenceLevel = $profile->home_presence_level ?? '';
        $this->smokes = (bool) $profile->smokes;
        $this->smokingLocation = $profile->smoking_location ?? '';
        $this->hasPet = (bool) $profile->has_pet;
        $this->socialLevel = $profile->social_level ?? '';
        $this->prefersQuiet = (bool) $profile->prefers_quiet;
        $this->cleanlinessLevel = $profile->cleanliness_level ?? '';
        $this->participatesInCleaning = (bool) $profile->participates_in_cleaning;
        $this->respectsPersonalSpace = (bool) $profile->respects_personal_space;
    }

    public function save(CoLivingProfileService $profiles, RoomOccupantSnapshotService $snapshots): void
    {
        $this->validate([
            'publicAlias' => ['nullable', 'string', 'max:80'],
            'ageRange' => ['nullable', 'string', 'max:20'],
            'genderForRoomPolicy' => ['nullable', 'string', 'max:40'],
            'languages' => ['nullable', 'string', 'max:120'],
            'stayPurpose' => ['nullable', 'string', 'max:60'],
            'guestType' => ['nullable', 'string', 'max:60'],
            'sleepSchedule' => ['nullable', 'string', 'max:60'],
            'wakeSchedule' => ['nullable', 'string', 'max:60'],
            'homePresenceLevel' => ['nullable', 'string', 'max:60'],
            'smokingLocation' => ['nullable', 'string', 'max:80'],
            'socialLevel' => ['nullable', 'string', 'max:60'],
            'cleanlinessLevel' => ['nullable', 'string', 'max:60'],
        ]);

        $profiles->updateProfile(auth()->user(), [
            'public_alias' => $this->publicAlias ?: null,
            'age_range' => $this->ageRange ?: null,
            'gender_for_room_policy' => $this->genderForRoomPolicy ?: null,
            'languages_json' => $this->languageList(),
            'stay_purpose' => $this->stayPurpose ?: null,
            'guest_type' => $this->guestType ?: null,
            'tourist' => $this->tourist,
            'student' => $this->student,
            'working' => $this->working,
            'remote_worker' => $this->remoteWorker,
            'long_term_guest' => $this->longTermGuest,
            'short_term_guest' => $this->shortTermGuest,
            'sleep_schedule' => $this->sleepSchedule ?: null,
            'wake_schedule' => $this->wakeSchedule ?: null,
            'home_presence_level' => $this->homePresenceLevel ?: null,
            'smokes' => $this->smokes,
            'smoking_location' => $this->smokingLocation ?: null,
            'has_pet' => $this->hasPet,
            'social_level' => $this->socialLevel ?: null,
            'prefers_quiet' => $this->prefersQuiet,
            'cleanliness_level' => $this->cleanlinessLevel ?: null,
            'participates_in_cleaning' => $this->participatesInCleaning,
            'respects_personal_space' => $this->respectsPersonalSpace,
            'profile_completed_at' => now(),
        ]);

        $snapshots->refreshForUser(auth()->user());

        session()->flash('co_living_status', __('occupants.messages.profile_saved'));
    }

    public function render(): View
    {
        return view('livewire.profile.co-living-profile-form');
    }

    /**
     * @return list<string>
     */
    private function languageList(): array
    {
        return collect(explode(',', $this->languages))
            ->map(fn (string $language): string => trim(strtolower($language)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
