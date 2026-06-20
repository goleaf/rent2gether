<?php

namespace App\Livewire\Profile;

use App\Models\GuestCompatibilityVisibilitySetting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestCompatibilityPrivacySettings extends Component
{
    public bool $showSmokingPreference = false;

    public bool $showSleepSchedule = true;

    public bool $showWorkStudyStatus = true;

    public bool $showHomePresence = false;

    public bool $showSocialLevel = true;

    public bool $showCleanlinessPreference = false;

    public bool $showRoomPreferences = true;

    public bool $showWorkspaceNeeds = true;

    public bool $showPetPreference = false;

    public bool $allowUseForMatching = true;

    public bool $allowShowToHosts = false;

    public bool $allowShowToFutureRoommates = false;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $settings = GuestCompatibilityVisibilitySetting::query()->firstOrCreate(['user_id' => $user->id]);
        $this->showSmokingPreference = $settings->show_smoking_preference;
        $this->showSleepSchedule = $settings->show_sleep_schedule;
        $this->showWorkStudyStatus = $settings->show_work_study_status;
        $this->showHomePresence = $settings->show_home_presence;
        $this->showSocialLevel = $settings->show_social_level;
        $this->showCleanlinessPreference = $settings->show_cleanliness_preference;
        $this->showRoomPreferences = $settings->show_room_preferences;
        $this->showWorkspaceNeeds = $settings->show_workspace_needs;
        $this->showPetPreference = $settings->show_pet_preference;
        $this->allowUseForMatching = $settings->allow_use_for_matching;
        $this->allowShowToHosts = $settings->allow_show_to_hosts;
        $this->allowShowToFutureRoommates = $settings->allow_show_to_future_roommates;
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        GuestCompatibilityVisibilitySetting::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'show_smoking_preference' => $this->showSmokingPreference,
                'show_sleep_schedule' => $this->showSleepSchedule,
                'show_work_study_status' => $this->showWorkStudyStatus,
                'show_home_presence' => $this->showHomePresence,
                'show_social_level' => $this->showSocialLevel,
                'show_cleanliness_preference' => $this->showCleanlinessPreference,
                'show_room_preferences' => $this->showRoomPreferences,
                'show_workspace_needs' => $this->showWorkspaceNeeds,
                'show_pet_preference' => $this->showPetPreference,
                'allow_use_for_matching' => $this->allowUseForMatching,
                'allow_show_to_hosts' => $this->allowShowToHosts,
                'allow_show_to_future_roommates' => $this->allowShowToFutureRoommates,
            ],
        );

        session()->flash('compatibility-privacy-status', __('compatibility.messages.privacy_saved'));
    }

    public function render(): View
    {
        return view('livewire.profile.guest-compatibility-privacy-settings');
    }
}
