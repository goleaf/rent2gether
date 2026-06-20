<?php

namespace App\Livewire\Profile;

use App\Services\Compatibility\GuestCompatibilityProfileService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestCompatibilityProfileForm extends Component
{
    public string $step = 'smoking_pets';

    public ?bool $smokes = null;

    public ?string $smokingPreference = null;

    public ?string $tobaccoSmellSensitivity = null;

    public ?bool $wakesUpEarly = null;

    public ?bool $wakesUpLate = null;

    public ?bool $sleepsEarly = null;

    public ?bool $sleepsLate = null;

    public ?bool $worksAtNight = null;

    public ?bool $studiesAtNight = null;

    public ?bool $returnsLate = null;

    public ?bool $needsLateEntry = null;

    public ?bool $needsQuietAtNight = null;

    public ?bool $sensitiveToLightAtNight = null;

    public ?bool $sensitiveToNoiseAtNight = null;

    public ?bool $student = null;

    public ?bool $working = null;

    public ?bool $remoteWorker = null;

    public ?bool $needsWorkspace = null;

    public ?bool $needsFastWifi = null;

    public ?bool $needsPowerSocket = null;

    public ?bool $needsOnlineCalls = null;

    public ?bool $oftenHome = null;

    public ?bool $rarelyHome = null;

    public ?bool $mostlyOnlySleeps = null;

    public ?bool $cooksOften = null;

    public ?bool $needsKitchen = null;

    public ?bool $needsFridgeShelf = null;

    public ?bool $needsWashingMachine = null;

    public ?string $socialLevel = null;

    public ?bool $prefersPrivateSpace = null;

    public ?bool $comfortableWithStrangers = null;

    public ?string $cleanlinessExpectation = null;

    public ?bool $readyToJoinCleaning = null;

    public ?bool $wantsPrivateRoom = null;

    public ?bool $comfortableWithSharedRoom = null;

    public ?int $maxPeopleInRoom = null;

    public ?bool $wantsFemaleRoom = null;

    public ?bool $wantsMaleRoom = null;

    public ?bool $comfortableWithMixedRoom = null;

    public ?bool $wantsLowerBunk = null;

    public ?bool $avoidsUpperBunk = null;

    public ?bool $avoidsSofa = null;

    public ?bool $avoidsFloorMattress = null;

    public ?bool $needsLocker = null;

    public ?bool $needsLockerLock = null;

    public ?bool $needsLuggageSpace = null;

    public ?bool $needsBedding = null;

    public ?bool $needsTowel = null;

    public ?bool $needsCurtain = null;

    public ?bool $travellingWithPet = null;

    public ?bool $avoidsPets = null;

    public ?bool $hasPetAllergy = null;

    public ?bool $needsSelfCheckIn = null;

    public ?bool $needs247Access = null;

    public function mount(GuestCompatibilityProfileService $profiles): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $profile = $profiles->getProfile($user);
        $this->smokes = $profile->smokes;
        $this->smokingPreference = $profile->smoking_preference;
        $this->tobaccoSmellSensitivity = $profile->tobacco_smell_sensitivity;
        $this->wakesUpEarly = $profile->wakes_up_early;
        $this->wakesUpLate = $profile->wakes_up_late;
        $this->sleepsEarly = $profile->sleeps_early;
        $this->sleepsLate = $profile->sleeps_late;
        $this->worksAtNight = $profile->works_at_night;
        $this->studiesAtNight = $profile->studies_at_night;
        $this->returnsLate = $profile->returns_late;
        $this->needsLateEntry = $profile->needs_late_entry;
        $this->needsQuietAtNight = $profile->needs_quiet_at_night;
        $this->sensitiveToLightAtNight = $profile->sensitive_to_light_at_night;
        $this->sensitiveToNoiseAtNight = $profile->sensitive_to_noise_at_night;
        $this->student = $profile->student;
        $this->working = $profile->working;
        $this->remoteWorker = $profile->remote_worker;
        $this->needsWorkspace = $profile->needs_workspace;
        $this->needsFastWifi = $profile->needs_fast_wifi;
        $this->needsPowerSocket = $profile->needs_power_socket;
        $this->needsOnlineCalls = $profile->needs_online_calls;
        $this->oftenHome = $profile->often_home;
        $this->rarelyHome = $profile->rarely_home;
        $this->mostlyOnlySleeps = $profile->mostly_only_sleeps;
        $this->cooksOften = $profile->cooks_often;
        $this->needsKitchen = $profile->needs_kitchen;
        $this->needsFridgeShelf = $profile->needs_fridge_shelf;
        $this->needsWashingMachine = $profile->needs_washing_machine;
        $this->socialLevel = $profile->social_level;
        $this->prefersPrivateSpace = $profile->prefers_private_space;
        $this->comfortableWithStrangers = $profile->comfortable_with_strangers;
        $this->cleanlinessExpectation = $profile->cleanliness_expectation;
        $this->readyToJoinCleaning = $profile->ready_to_join_cleaning;
        $this->wantsPrivateRoom = $profile->wants_private_room;
        $this->comfortableWithSharedRoom = $profile->comfortable_with_shared_room;
        $this->maxPeopleInRoom = $profile->max_people_in_room;
        $this->wantsFemaleRoom = $profile->wants_female_room;
        $this->wantsMaleRoom = $profile->wants_male_room;
        $this->comfortableWithMixedRoom = $profile->comfortable_with_mixed_room;
        $this->wantsLowerBunk = $profile->wants_lower_bunk;
        $this->avoidsUpperBunk = $profile->avoids_upper_bunk;
        $this->avoidsSofa = $profile->avoids_sofa;
        $this->avoidsFloorMattress = $profile->avoids_floor_mattress;
        $this->needsLocker = $profile->needs_locker;
        $this->needsLockerLock = $profile->needs_locker_lock;
        $this->needsLuggageSpace = $profile->needs_luggage_space;
        $this->needsBedding = $profile->needs_bedding;
        $this->needsTowel = $profile->needs_towel;
        $this->needsCurtain = $profile->needs_curtain;
        $this->travellingWithPet = $profile->travelling_with_pet;
        $this->avoidsPets = $profile->avoids_pets;
        $this->hasPetAllergy = $profile->has_pet_allergy;
        $this->needsSelfCheckIn = $profile->needs_self_check_in;
        $this->needs247Access = $profile->needs_24_7_access;
    }

    public function save(GuestCompatibilityProfileService $profiles): void
    {
        $validated = $this->validate([
            'step' => ['required', 'string'],
            'smokes' => ['nullable', 'boolean'],
            'smokingPreference' => ['nullable', 'string', 'max:50'],
            'tobaccoSmellSensitivity' => ['nullable', 'string', 'max:50'],
            'maxPeopleInRoom' => ['nullable', 'integer', 'min:1', 'max:12'],
        ], attributes: [
            'maxPeopleInRoom' => __('compatibility.fields.max_people_in_room'),
        ]);

        unset($validated['step']);

        $user = auth()->user();
        abort_unless($user, 403);

        $profiles->updateProfile($user, [
            ...$this->payload(),
            'max_people_in_room' => $validated['maxPeopleInRoom'] ?? $this->maxPeopleInRoom,
        ]);
        $profiles->completeProfile($user);

        session()->flash('compatibility-status', __('compatibility.messages.profile_saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'smokes' => $this->smokes,
            'smoking_preference' => $this->smokingPreference,
            'tobacco_smell_sensitivity' => $this->tobaccoSmellSensitivity,
            'wakes_up_early' => $this->wakesUpEarly,
            'wakes_up_late' => $this->wakesUpLate,
            'sleeps_early' => $this->sleepsEarly,
            'sleeps_late' => $this->sleepsLate,
            'works_at_night' => $this->worksAtNight,
            'studies_at_night' => $this->studiesAtNight,
            'returns_late' => $this->returnsLate,
            'needs_late_entry' => $this->needsLateEntry,
            'needs_quiet_at_night' => $this->needsQuietAtNight,
            'sensitive_to_light_at_night' => $this->sensitiveToLightAtNight,
            'sensitive_to_noise_at_night' => $this->sensitiveToNoiseAtNight,
            'student' => $this->student,
            'working' => $this->working,
            'remote_worker' => $this->remoteWorker,
            'needs_workspace' => $this->needsWorkspace,
            'needs_fast_wifi' => $this->needsFastWifi,
            'needs_power_socket' => $this->needsPowerSocket,
            'needs_online_calls' => $this->needsOnlineCalls,
            'often_home' => $this->oftenHome,
            'rarely_home' => $this->rarelyHome,
            'mostly_only_sleeps' => $this->mostlyOnlySleeps,
            'cooks_often' => $this->cooksOften,
            'needs_kitchen' => $this->needsKitchen,
            'needs_fridge_shelf' => $this->needsFridgeShelf,
            'needs_washing_machine' => $this->needsWashingMachine,
            'social_level' => $this->socialLevel,
            'prefers_private_space' => $this->prefersPrivateSpace,
            'comfortable_with_strangers' => $this->comfortableWithStrangers,
            'cleanliness_expectation' => $this->cleanlinessExpectation,
            'ready_to_join_cleaning' => $this->readyToJoinCleaning,
            'wants_private_room' => $this->wantsPrivateRoom,
            'comfortable_with_shared_room' => $this->comfortableWithSharedRoom,
            'max_people_in_room' => $this->maxPeopleInRoom,
            'wants_female_room' => $this->wantsFemaleRoom,
            'wants_male_room' => $this->wantsMaleRoom,
            'comfortable_with_mixed_room' => $this->comfortableWithMixedRoom,
            'wants_lower_bunk' => $this->wantsLowerBunk,
            'avoids_upper_bunk' => $this->avoidsUpperBunk,
            'avoids_sofa' => $this->avoidsSofa,
            'avoids_floor_mattress' => $this->avoidsFloorMattress,
            'needs_locker' => $this->needsLocker,
            'needs_locker_lock' => $this->needsLockerLock,
            'needs_luggage_space' => $this->needsLuggageSpace,
            'needs_bedding' => $this->needsBedding,
            'needs_towel' => $this->needsTowel,
            'needs_curtain' => $this->needsCurtain,
            'travelling_with_pet' => $this->travellingWithPet,
            'avoids_pets' => $this->avoidsPets,
            'has_pet_allergy' => $this->hasPetAllergy,
            'needs_self_check_in' => $this->needsSelfCheckIn,
            'needs_24_7_access' => $this->needs247Access,
        ];
    }

    public function render(): View
    {
        return view('livewire.profile.guest-compatibility-profile-form');
    }
}
