<?php

namespace App\Livewire\Account;

use App\Actions\Account\StoreAvatarVariants;
use App\Enums\UserStatus;
use App\Livewire\Concerns\UsesAccountValidationAttributes;
use App\Models\City;
use App\Models\Country;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\UserProfile;
use App\Models\UserSetting;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileSetupPage extends Component
{
    use UsesAccountValidationAttributes;
    use WithFileUploads;

    public string $displayName = '';

    public $avatar = null;

    public ?string $savedAvatarPath = null;

    public string $phone = '';

    public string $country = '';

    public string $city = '';

    public string $languages = '';

    public ?string $dateOfBirth = null;

    public string $gender = '';

    public string $about = '';

    public string $occupation = '';

    public string $travelPurpose = '';

    public bool $smokes = false;

    public bool $hasPets = false;

    public string $allergies = '';

    public bool $prefersQuiet = false;

    public string $sleepSchedule = '';

    public string $socialLevel = '';

    public string $accountRole = 'guest';

    public function mount(): void
    {
        $user = auth()->user();
        $profile = $user->profile;

        $this->displayName = $profile?->display_name ?: $user->name;
        $this->savedAvatarPath = $profile?->avatar_path ?: $user->avatar;
        $this->phone = $profile?->phone ?: ($user->phone ?? '');
        $this->country = $profile?->country?->localizedName() ?: ($user->country ?? '');
        $this->city = $profile?->city?->name ?: ($user->city ?? '');
        $this->languages = implode(', ', $profile?->languages_json ?: ($user->languages ?? []));
        $this->dateOfBirth = $profile?->date_of_birth?->format('Y-m-d') ?: $user->date_of_birth?->format('Y-m-d');
        $this->gender = $profile?->gender ?: ($user->gender ?? '');
        $this->about = $profile?->about ?: ($user->bio ?? '');
        $this->occupation = $profile?->occupation ?: ($user->occupation ?? '');
        $this->travelPurpose = $profile?->travel_purpose ?: ($user->travel_purpose ?? '');
        $this->smokes = (bool) ($profile?->smokes ?? $user->is_smoker);
        $this->hasPets = (bool) ($profile?->has_pets ?? $user->has_pets);
        $this->allergies = $profile?->allergies ?: '';
        $this->prefersQuiet = (bool) ($profile?->prefers_quiet ?? $user->prefers_quiet);
        $this->sleepSchedule = $profile?->sleep_schedule ?: ($user->sleep_schedule ?? '');
        $this->socialLevel = $profile?->social_level ?: '';
        $this->accountRole = $user->setting?->account_role ?: ($user->is_host ? UserSetting::ROLE_BOTH : UserSetting::ROLE_GUEST);
    }

    public function save(StoreAvatarVariants $avatars): void
    {
        $validated = $this->validate([
            'displayName' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
            'phone' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'languages' => ['nullable', 'string', 'max:120'],
            'dateOfBirth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'not_specified'])],
            'about' => ['nullable', 'string', 'max:1000'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'travelPurpose' => ['nullable', 'string', 'max:120'],
            'smokes' => ['boolean'],
            'hasPets' => ['boolean'],
            'allergies' => ['nullable', 'string', 'max:500'],
            'prefersQuiet' => ['boolean'],
            'sleepSchedule' => ['nullable', Rule::in(['early_bird', 'night_owl', 'flexible', 'regular'])],
            'socialLevel' => ['nullable', Rule::in(['quiet', 'balanced', 'social'])],
            'accountRole' => ['required', Rule::in(['guest', 'host', 'both'])],
        ], attributes: $this->accountValidationAttributes());

        $user = auth()->user();
        $country = $this->countryModel($validated['country'] ?: null);
        $city = $this->cityModel($validated['city'] ?: null);
        $languageList = $this->languageList($validated['languages'] ?: null);
        $isHost = in_array($validated['accountRole'], ['host', 'both'], true);
        $avatarPath = $user->profile?->avatar_path ?: $user->avatar;

        if ($this->avatar) {
            $paths = $avatars->handle($user, $this->avatar);
            $avatarPath = $paths['medium'];
        }

        $user->update([
            'name' => $validated['displayName'],
            'phone' => $validated['phone'] ?: null,
            'avatar' => $avatarPath,
            'date_of_birth' => $validated['dateOfBirth'] ?: null,
            'gender' => $validated['gender'] ?: null,
            'country' => $validated['country'] ?: null,
            'city' => $validated['city'] ?: null,
            'languages' => $languageList,
            'bio' => $validated['about'] ?: null,
            'occupation' => $validated['occupation'] ?: null,
            'travel_purpose' => $validated['travelPurpose'] ?: null,
            'is_smoker' => $validated['smokes'],
            'has_pets' => $validated['hasPets'],
            'has_allergies' => $validated['allergies'] !== null && $validated['allergies'] !== '',
            'prefers_quiet' => $validated['prefersQuiet'],
            'sleep_schedule' => $validated['sleepSchedule'] ?: null,
            'is_host' => $isHost,
        ]);

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $validated['displayName'],
                'avatar_path' => $avatarPath,
                'date_of_birth' => $validated['dateOfBirth'] ?: null,
                'gender' => $validated['gender'] ?: null,
                'country_id' => $country?->id,
                'city_id' => $city?->id,
                'phone' => $validated['phone'] ?: null,
                'email_verified_at' => $user->email_verified_at,
                'about' => $validated['about'] ?: null,
                'languages_json' => $languageList,
                'occupation' => $validated['occupation'] ?: null,
                'travel_purpose' => $validated['travelPurpose'] ?: null,
                'smokes' => $validated['smokes'],
                'has_pets' => $validated['hasPets'],
                'allergies' => $validated['allergies'] ?: null,
                'prefers_quiet' => $validated['prefersQuiet'],
                'sleep_schedule' => $validated['sleepSchedule'] ?: null,
                'social_level' => $validated['socialLevel'] ?: null,
                'status' => UserStatus::Active,
            ],
        );

        GuestPreference::query()->firstOrCreate(['user_id' => $user->id]);

        if ($isHost) {
            HostProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $validated['displayName'],
                    'avatar_path' => $avatarPath,
                    'about' => $validated['about'] ?: null,
                    'languages_json' => $languageList,
                    'status' => UserStatus::Active,
                ],
            );
        }

        $user->setting()->updateOrCreate([], [
            'active_mode' => $isHost ? UserSetting::MODE_HOST : UserSetting::MODE_GUEST,
            'account_role' => $validated['accountRole'],
        ]);
        session()->put('account_mode', $isHost ? UserSetting::MODE_HOST : UserSetting::MODE_GUEST);

        $this->savedAvatarPath = $avatarPath;
        $this->avatar = null;
        session()->flash('success', __('notifications.flash.profile_updated'));
    }

    public function render(): View
    {
        return view('livewire.account.profile-setup-page')
            ->layout('layouts.app', ['title' => __('account.profile_setup.title')]);
    }

    private function countryModel(?string $name): ?Country
    {
        if (! $name) {
            return null;
        }

        return Country::query()
            ->where('name_normalized', GeoNameNormalizer::normalize($name))
            ->orWhere('iso2', strtoupper($name))
            ->first();
    }

    private function cityModel(?string $name): ?City
    {
        if (! $name) {
            return null;
        }

        return City::query()
            ->where('name_normalized', GeoNameNormalizer::normalize($name))
            ->first();
    }

    /**
     * @return list<string>
     */
    private function languageList(?string $languages): array
    {
        return collect(explode(',', (string) $languages))
            ->map(fn (string $language): string => str($language)->trim()->lower()->toString())
            ->filter()
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }
}
