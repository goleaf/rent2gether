<?php

namespace App\Livewire\Host\Concerns;

use App\Actions\Account\StoreAvatarVariants;
use App\Enums\UserStatus;
use App\Models\HostProfile;
use App\Models\UserSetting;
use Illuminate\Validation\Rule;

trait ManagesHostProfileForm
{
    public string $displayName = '';

    public $avatar = null;

    public string $about = '';

    public string $languages = '';

    public string $responseStyle = 'friendly';

    public bool $livesInProperty = false;

    public bool $livesNearby = false;

    public bool $canHelpWithCheckIn = false;

    public bool $emergencyContactAvailable = false;

    public string $hostingExperience = '';

    public string $defaultCheckInTime = '';

    public string $defaultCheckOutTime = '';

    public string $defaultCancellationPolicy = 'flexible';

    public string $defaultDepositSetting = 'none';

    public string $defaultHouseRules = '';

    protected function mountHostProfileForm(): void
    {
        $user = auth()->user();
        $profile = $user->hostProfile;

        $this->displayName = $profile?->display_name ?: $user->name;
        $this->about = $profile?->about ?: '';
        $this->languages = implode(', ', $profile?->languages_json ?: ($user->languages ?? []));
        $this->responseStyle = $profile?->response_style ?: 'friendly';
        $this->livesInProperty = (bool) ($profile?->lives_in_property ?? false);
        $this->livesNearby = (bool) ($profile?->lives_nearby ?? false);
        $this->canHelpWithCheckIn = (bool) ($profile?->can_help_with_check_in ?? false);
        $this->emergencyContactAvailable = (bool) ($profile?->emergency_contact_available ?? false);
        $this->hostingExperience = $profile?->hosting_experience ?: '';
        $this->defaultCheckInTime = $this->formatTime($profile?->default_check_in_time);
        $this->defaultCheckOutTime = $this->formatTime($profile?->default_check_out_time);
        $this->defaultCancellationPolicy = $profile?->default_cancellation_policy ?: 'flexible';
        $this->defaultDepositSetting = $profile?->default_deposit_setting ?: 'none';
        $this->defaultHouseRules = $profile?->default_house_rules ?: '';
    }

    /** @return array<string, mixed> */
    protected function hostProfileRules(): array
    {
        return [
            'displayName' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
            'about' => ['nullable', 'string', 'max:1000'],
            'languages' => ['nullable', 'string', 'max:120'],
            'responseStyle' => ['required', Rule::in(['friendly', 'quick', 'detailed'])],
            'livesInProperty' => ['boolean'],
            'livesNearby' => ['boolean'],
            'canHelpWithCheckIn' => ['boolean'],
            'emergencyContactAvailable' => ['boolean'],
            'hostingExperience' => ['nullable', Rule::in(['new_host', 'some_experience', 'experienced'])],
            'defaultCheckInTime' => ['nullable', 'date_format:H:i'],
            'defaultCheckOutTime' => ['nullable', 'date_format:H:i'],
            'defaultCancellationPolicy' => ['required', Rule::in(['flexible', 'moderate', 'strict', 'non_refundable'])],
            'defaultDepositSetting' => ['required', Rule::in(['none', 'small', 'standard', 'custom'])],
            'defaultHouseRules' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @param array<string, mixed> $validated */
    protected function persistHostProfile(array $validated, StoreAvatarVariants $avatars): HostProfile
    {
        $user = auth()->user();
        $avatarPath = $user->hostProfile?->avatar_path ?: $user->avatar;

        if ($this->avatar) {
            $paths = $avatars->handle($user, $this->avatar);
            $avatarPath = $paths['medium'];
        }

        $languageList = $this->languageList($validated['languages'] ?: null);

        $user->update([
            'is_host' => true,
            'avatar' => $avatarPath ?: $user->avatar,
        ]);

        $setting = $user->setting;
        $accountRole = in_array($setting?->account_role, [null, UserSetting::ROLE_GUEST], true)
            ? UserSetting::ROLE_BOTH
            : ($setting?->account_role ?: UserSetting::ROLE_HOST);

        $user->setting()->updateOrCreate([], [
            'active_mode' => UserSetting::MODE_HOST,
            'account_role' => $accountRole,
        ]);
        session()->put('account_mode', UserSetting::MODE_HOST);

        $profile = $user->hostProfile()->updateOrCreate([], [
            'display_name' => $validated['displayName'],
            'avatar_path' => $avatarPath,
            'about' => $validated['about'] ?: null,
            'languages_json' => $languageList,
            'response_style' => $validated['responseStyle'],
            'response_time_minutes' => $this->responseMinutes($validated['responseStyle']),
            'lives_in_property' => $validated['livesInProperty'],
            'lives_nearby' => $validated['livesNearby'],
            'can_help_with_check_in' => $validated['canHelpWithCheckIn'],
            'emergency_contact_available' => $validated['emergencyContactAvailable'],
            'hosting_experience' => $validated['hostingExperience'] ?: null,
            'default_check_in_time' => $validated['defaultCheckInTime'] ?: null,
            'default_check_out_time' => $validated['defaultCheckOutTime'] ?: null,
            'default_cancellation_policy' => $validated['defaultCancellationPolicy'],
            'default_deposit_setting' => $validated['defaultDepositSetting'],
            'default_house_rules' => $validated['defaultHouseRules'] ?: null,
            'status' => UserStatus::Active,
        ]);

        $user->setRelation('hostProfile', $profile);
        $this->avatar = null;

        return $profile;
    }

    /** @return list<array{label:string, done:bool}> */
    public function readinessChecklist(): array
    {
        return [
            ['label' => __('host.profile.checklist.photo'), 'done' => (bool) (auth()->user()->hostProfile?->avatar_path ?: auth()->user()->avatar ?: $this->avatar)],
            ['label' => __('host.profile.checklist.about'), 'done' => filled($this->about)],
            ['label' => __('host.profile.checklist.languages'), 'done' => $this->languageList($this->languages) !== []],
            ['label' => __('host.profile.checklist.default_rules'), 'done' => filled($this->defaultHouseRules)],
            ['label' => __('host.profile.checklist.default_times'), 'done' => filled($this->defaultCheckInTime) && filled($this->defaultCheckOutTime)],
            ['label' => __('host.profile.checklist.payout_placeholder'), 'done' => false],
        ];
    }

    /** @return list<string> */
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

    private function responseMinutes(string $style): ?int
    {
        return match ($style) {
            'quick' => 60,
            'detailed' => 720,
            default => 180,
        };
    }

    private function formatTime(mixed $time): string
    {
        if (! $time) {
            return '';
        }

        return str((string) $time)->substr(0, 5)->toString();
    }
}
