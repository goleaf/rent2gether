<?php

namespace App\Services\HostCalendar;

use App\Models\HostCalendarViewSetting;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class HostCalendarViewSettingsService
{
    public function getForUser(User $host): HostCalendarViewSetting
    {
        return HostCalendarViewSetting::query()->firstOrCreate(['user_id' => $host->id]);
    }

    public function updateForUser(User $host, array $data): HostCalendarViewSetting
    {
        if (isset($data['default_property_id']) && ! Property::query()->where('id', $data['default_property_id'])->where('host_user_id', $host->id)->exists()) {
            throw new AuthorizationException;
        }

        if (isset($data['default_room_id']) && ! Room::query()->where('id', $data['default_room_id'])->whereHas('property', fn ($property) => $property->where('host_user_id', $host->id))->exists()) {
            throw new AuthorizationException;
        }

        $settings = $this->getForUser($host);
        $settings->fill(array_intersect_key($data, array_flip($settings->getFillable())))->save();

        return $settings->refresh();
    }
}
