<?php

namespace App\Livewire\Profile;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class EditProfile extends Component
{
    public string $name = '';

    public string $phone = '';

    public ?string $dateOfBirth = null;

    public string $gender = '';

    public string $country = '';

    public string $city = '';

    public string $bio = '';

    public string $occupation = '';

    public string $travelPurpose = '';

    public bool $isSmoker = false;

    public bool $hasPets = false;

    public bool $hasAllergies = false;

    public bool $prefersQuiet = false;

    public string $sleepSchedule = '';

    public bool $willingToShareRoom = true;

    public string $preferredRoomGender = '';

    public bool $isHost = false;

    public string $hostDescription = '';

    public ?int $hostExperienceYears = null;

    public bool $hostLivesOnSite = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name ?? '';
        $this->phone = $user->phone ?? '';
        $this->dateOfBirth = $user->date_of_birth?->format('Y-m-d');
        $this->gender = $user->gender ?? '';
        $this->country = $user->country ?? '';
        $this->city = $user->city ?? '';
        $this->bio = $user->bio ?? '';
        $this->occupation = $user->occupation ?? '';
        $this->travelPurpose = $user->travel_purpose ?? '';
        $this->isSmoker = (bool) $user->is_smoker;
        $this->hasPets = (bool) $user->has_pets;
        $this->hasAllergies = (bool) $user->has_allergies;
        $this->prefersQuiet = (bool) $user->prefers_quiet;
        $this->sleepSchedule = $user->sleep_schedule ?? '';
        $this->willingToShareRoom = (bool) $user->willing_to_share_room;
        $this->preferredRoomGender = $user->preferred_room_gender ?? '';
        $this->isHost = (bool) $user->is_host;
        $this->hostDescription = $user->host_description ?? '';
        $this->hostExperienceYears = $user->host_experience_years;
        $this->hostLivesOnSite = (bool) $user->host_lives_on_site;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'dateOfBirth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'isHost' => ['boolean'],
            'hostDescription' => ['nullable', 'string', 'max:2000'],
            'hostExperienceYears' => ['nullable', 'integer', 'min:0', 'max:100'],
            'hostLivesOnSite' => ['boolean'],
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'date_of_birth' => $this->dateOfBirth ?: null,
            'gender' => $this->gender ?: null,
            'country' => $this->country ?: null,
            'city' => $this->city ?: null,
            'bio' => $this->bio ?: null,
            'occupation' => $this->occupation ?: null,
            'travel_purpose' => $this->travelPurpose ?: null,
            'is_smoker' => $this->isSmoker,
            'has_pets' => $this->hasPets,
            'has_allergies' => $this->hasAllergies,
            'prefers_quiet' => $this->prefersQuiet,
            'sleep_schedule' => $this->sleepSchedule ?: null,
            'willing_to_share_room' => $this->willingToShareRoom,
            'preferred_room_gender' => $this->preferredRoomGender ?: null,
            'is_host' => $this->isHost,
            'host_description' => $this->isHost ? ($this->hostDescription ?: null) : null,
            'host_experience_years' => $this->isHost ? $this->hostExperienceYears : null,
            'host_lives_on_site' => $this->isHost && $this->hostLivesOnSite,
        ]);

        session()->flash('success', __('notifications.flash.profile_updated'));
    }

    public function render(): View
    {
        return view('livewire.profile.edit-profile');
    }
}
