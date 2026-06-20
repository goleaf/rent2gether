<?php

namespace App\Livewire\Profile;

use App\Services\Occupants\CoLivingPrivacyService;
use App\Services\Occupants\RoomOccupantSnapshotService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CoLivingPrivacySettings extends Component
{
    public bool $showPublicAlias = true;

    public bool $showRealFirstName = false;

    public bool $showAvatar = false;

    public bool $showAgeRange = true;

    public bool $showGenderIfRoomPolicy = true;

    public bool $showCountry = false;

    public bool $showCity = false;

    public bool $showLanguages = true;

    public bool $showStayPurpose = true;

    public bool $showGuestType = true;

    public bool $showSleepSchedule = true;

    public bool $showWakeSchedule = false;

    public bool $showHomePresence = true;

    public bool $showSmokingStatus = true;

    public bool $showPetStatus = false;

    public bool $showSocialLevel = true;

    public bool $showQuietPreference = true;

    public bool $showCleanlinessLevel = false;

    public bool $showRoommateRating = true;

    public bool $showCheckoutDateToFutureRoommates = true;

    public bool $allowProfileInPrebookingSummary = true;

    public bool $allowProfileAfterConfirmedBooking = true;

    public function mount(CoLivingPrivacyService $privacy): void
    {
        $settings = $privacy->settingsFor(auth()->user());

        $this->showPublicAlias = (bool) $settings->show_public_alias;
        $this->showRealFirstName = (bool) $settings->show_real_first_name;
        $this->showAvatar = (bool) $settings->show_avatar;
        $this->showAgeRange = (bool) $settings->show_age_range;
        $this->showGenderIfRoomPolicy = (bool) $settings->show_gender_if_room_policy;
        $this->showCountry = (bool) $settings->show_country;
        $this->showCity = (bool) $settings->show_city;
        $this->showLanguages = (bool) $settings->show_languages;
        $this->showStayPurpose = (bool) $settings->show_stay_purpose;
        $this->showGuestType = (bool) $settings->show_guest_type;
        $this->showSleepSchedule = (bool) $settings->show_sleep_schedule;
        $this->showWakeSchedule = (bool) $settings->show_wake_schedule;
        $this->showHomePresence = (bool) $settings->show_home_presence;
        $this->showSmokingStatus = (bool) $settings->show_smoking_status;
        $this->showPetStatus = (bool) $settings->show_pet_status;
        $this->showSocialLevel = (bool) $settings->show_social_level;
        $this->showQuietPreference = (bool) $settings->show_quiet_preference;
        $this->showCleanlinessLevel = (bool) $settings->show_cleanliness_level;
        $this->showRoommateRating = (bool) $settings->show_roommate_rating;
        $this->showCheckoutDateToFutureRoommates = (bool) $settings->show_checkout_date_to_future_roommates;
        $this->allowProfileInPrebookingSummary = (bool) $settings->allow_profile_in_prebooking_summary;
        $this->allowProfileAfterConfirmedBooking = (bool) $settings->allow_profile_after_confirmed_booking;
    }

    public function save(CoLivingPrivacyService $privacy, RoomOccupantSnapshotService $snapshots): void
    {
        $settings = $privacy->settingsFor(auth()->user());
        $settings->fill([
            'show_public_alias' => $this->showPublicAlias,
            'show_real_first_name' => $this->showRealFirstName,
            'show_avatar' => $this->showAvatar,
            'show_age_range' => $this->showAgeRange,
            'show_gender_if_room_policy' => $this->showGenderIfRoomPolicy,
            'show_country' => $this->showCountry,
            'show_city' => $this->showCity,
            'show_languages' => $this->showLanguages,
            'show_stay_purpose' => $this->showStayPurpose,
            'show_guest_type' => $this->showGuestType,
            'show_sleep_schedule' => $this->showSleepSchedule,
            'show_wake_schedule' => $this->showWakeSchedule,
            'show_home_presence' => $this->showHomePresence,
            'show_smoking_status' => $this->showSmokingStatus,
            'show_pet_status' => $this->showPetStatus,
            'show_social_level' => $this->showSocialLevel,
            'show_quiet_preference' => $this->showQuietPreference,
            'show_cleanliness_level' => $this->showCleanlinessLevel,
            'show_roommate_rating' => $this->showRoommateRating,
            'show_checkout_date_to_future_roommates' => $this->showCheckoutDateToFutureRoommates,
            'allow_profile_in_prebooking_summary' => $this->allowProfileInPrebookingSummary,
            'allow_profile_after_confirmed_booking' => $this->allowProfileAfterConfirmedBooking,
        ])->save();

        $snapshots->refreshForUser(auth()->user());

        session()->flash('co_living_status', __('occupants.messages.privacy_saved'));
    }

    public function render(): View
    {
        return view('livewire.profile.co-living-privacy-settings');
    }
}
