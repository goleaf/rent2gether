<?php

namespace App\Services;

use App\Models\HostProfile;
use App\Models\User;
use Illuminate\Support\Arr;

class HostProfileService
{
    private const FIELDS = [
        'display_name',
        'host_display_name',
        'host_type',
        'about',
        'about_host',
        'languages_json',
        'response_time_minutes',
        'response_rate',
        'acceptance_rate',
        'verified_host',
        'hosting_since',
        'default_currency',
        'default_language',
        'public_phone_visible',
        'public_email_visible',
        'representative_name',
        'representative_contact',
        'representative_visible_to_guest',
    ];

    public function getOrCreate(User $host): HostProfile
    {
        return HostProfile::query()->firstOrCreate(
            ['user_id' => $host->id],
            ['display_name' => $host->name, 'host_display_name' => $host->name],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $host, array $data): HostProfile
    {
        $profile = $this->getOrCreate($host);
        $profile->fill(Arr::only($data, self::FIELDS));
        $profile->save();

        return $profile->refresh();
    }
}
