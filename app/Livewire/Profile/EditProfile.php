<?php

namespace App\Livewire\Profile;

use App\Livewire\Concerns\HandlesCountryCityAutocomplete;
use App\Models\City;
use App\Models\Country;
use App\Models\UserProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EditProfile extends Component
{
    use HandlesCountryCityAutocomplete;

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

    public ?int $hostExperienceStartedYear = null;

    public ?int $hostExperienceYears = null;

    public bool $hostLivesOnSite = false;

    public function mount(): void
    {
        $user = auth()->user();
        $user->loadMissing(['profile.country', 'profile.city']);

        $this->name = $user->name ?? '';
        $this->phone = $user->phone ?? '';
        $this->dateOfBirth = $user->date_of_birth?->format('Y-m-d');
        $this->gender = $user->gender ?? '';
        $this->setCountryCityAutocomplete($user->profile?->country, $user->profile?->city, $user->country ?? '', $user->city ?? '');
        $this->country = $this->countryQuery;
        $this->city = $this->cityQuery;
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
        $this->hostExperienceStartedYear = $user->host_experience_started_year
            ?: $this->startedYearFromExperienceYears($user->host_experience_years);
        $this->hostExperienceYears = $this->calculateExperienceYears($this->hostExperienceStartedYear);
        $this->hostLivesOnSite = (bool) $user->host_lives_on_site;
    }

    public function updatedHostExperienceStartedYear(): void
    {
        $this->hostExperienceStartedYear = $this->blankToNullInt($this->hostExperienceStartedYear);
        $this->hostExperienceYears = $this->calculateExperienceYears($this->hostExperienceStartedYear);
    }

    public function save(): void
    {
        $attributes = app('translator')->get('account.validation_attributes');
        $this->countryQuery = $this->countryQuery ?: $this->country;
        $this->cityQuery = $this->cityQuery ?: $this->city;
        $this->resolveCountryCityAutocompleteIdsFromQueries();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'dateOfBirth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'countryQuery' => ['nullable', 'string', 'max:120'],
            'countryId' => [$this->countryQuery !== '' ? 'required' : 'nullable', 'integer', 'exists:countries,id'],
            'cityQuery' => ['nullable', 'string', 'max:120'],
            'cityId' => [
                $this->cityQuery !== '' ? 'required' : 'nullable',
                'integer',
                Rule::exists('cities', 'id')->where('country_id', $this->countryId),
            ],
            'bio' => ['nullable', 'string', 'max:2000'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'isHost' => ['boolean'],
            'hostDescription' => ['nullable', 'string', 'max:2000'],
            'hostExperienceStartedYear' => ['nullable', 'integer', 'min:'.$this->minimumExperienceStartYear(), 'max:'.$this->currentYear()],
            'hostExperienceYears' => ['nullable', 'integer', 'min:0', 'max:'.($this->currentYear() - $this->minimumExperienceStartYear())],
            'hostLivesOnSite' => ['boolean'],
        ], attributes: is_array($attributes) ? $attributes : []);

        $this->hostExperienceStartedYear = $this->blankToNullInt($this->hostExperienceStartedYear);
        $this->hostExperienceYears = $this->blankToNullInt($this->hostExperienceYears);

        if ($this->hostExperienceStartedYear === null && $this->hostExperienceYears !== null) {
            $this->hostExperienceStartedYear = $this->startedYearFromExperienceYears($this->hostExperienceYears);
        }

        $this->hostExperienceYears = $this->calculateExperienceYears($this->hostExperienceStartedYear);
        $country = $this->selectedAutocompleteCountry();
        $city = $this->selectedAutocompleteCity();
        $countryName = $country?->localizedName();
        $cityName = $city?->localizedName();

        auth()->user()->update([
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'date_of_birth' => $this->dateOfBirth ?: null,
            'gender' => $this->gender ?: null,
            'country' => $countryName,
            'city' => $cityName,
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
            'host_experience_started_year' => $this->isHost ? $this->hostExperienceStartedYear : null,
            'host_experience_years' => $this->isHost ? $this->hostExperienceYears : null,
            'host_lives_on_site' => $this->isHost && $this->hostLivesOnSite,
        ]);

        UserProfile::query()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'display_name' => $this->name,
                'country_id' => $country?->id,
                'city_id' => $city?->id,
                'public_city_name' => $cityName,
                'about' => $this->bio ?: null,
                'occupation' => $this->occupation ?: null,
            ],
        );

        session()->flash('success', __('notifications.flash.profile_updated'));
    }

    public function render(): View
    {
        return view('livewire.profile.edit-profile');
    }

    /** @return list<int> */
    #[Computed]
    public function hostExperienceYearOptions(): array
    {
        return range($this->currentYear(), $this->minimumExperienceStartYear());
    }

    #[Computed]
    public function calculatedHostExperienceYears(): ?int
    {
        return $this->calculateExperienceYears($this->blankToNullInt($this->hostExperienceStartedYear));
    }

    private function calculateExperienceYears(?int $startedYear): ?int
    {
        if ($startedYear === null) {
            return null;
        }

        return max(0, $this->currentYear() - $startedYear);
    }

    private function startedYearFromExperienceYears(?int $experienceYears): ?int
    {
        if ($experienceYears === null || $experienceYears < 0) {
            return null;
        }

        return max($this->minimumExperienceStartYear(), $this->currentYear() - $experienceYears);
    }

    private function blankToNullInt(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    private function currentYear(): int
    {
        return (int) now()->year;
    }

    private function minimumExperienceStartYear(): int
    {
        return 1970;
    }

    protected function afterCountrySelected(Country $country): void
    {
        $this->country = $country->localizedName();
        $this->city = '';
    }

    protected function afterCitySelected(City $city): void
    {
        $this->city = $city->localizedName();
    }
}
