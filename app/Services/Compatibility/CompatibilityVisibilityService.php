<?php

namespace App\Services\Compatibility;

use App\Models\GuestCompatibilityVisibilitySetting;
use App\Models\User;
use Illuminate\Support\Arr;

class CompatibilityVisibilityService
{
    /** @var list<string> */
    private array $publicKeys = [
        'sleep_schedule',
        'work_study_status',
        'social_level',
        'room_preferences',
        'workspace_needs',
    ];

    public function canUseProfileForMatching(User $user): bool
    {
        return $this->settings($user)->allow_use_for_matching;
    }

    public function canShowToHost(User $user): bool
    {
        return $this->settings($user)->allow_show_to_hosts;
    }

    public function canShowToFutureRoommates(User $user): bool
    {
        return $this->settings($user)->allow_show_to_future_roommates;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function filterForPublicDisplay(array $data): array
    {
        return Arr::only($data, $this->publicKeys);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function filterForHostDisplay(array $data): array
    {
        return Arr::except($data, [
            'phone',
            'email',
            'date_of_birth',
            'documents',
            'exact_workplace',
            'exact_school',
            'private_notes',
            'internal_flags',
            'complaint_details',
            'private_messages',
        ]);
    }

    public function settings(User $user): GuestCompatibilityVisibilitySetting
    {
        return GuestCompatibilityVisibilitySetting::query()->firstOrCreate(
            ['user_id' => $user->id],
            [],
        );
    }
}
