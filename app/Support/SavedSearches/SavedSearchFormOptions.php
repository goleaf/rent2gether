<?php

namespace App\Support\SavedSearches;

use App\Enums\GenderType;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceType;

final class SavedSearchFormOptions
{
    /**
     * @return list<string>
     */
    public static function notificationFrequencies(): array
    {
        return ['on_visit', 'instant', 'daily', 'weekly', 'important_only'];
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return ['active', 'paused', 'archived'];
    }

    /**
     * @return list<string>
     */
    public static function roomTypes(): array
    {
        return array_map(fn (RoomType $type): string => $type->value, RoomType::cases());
    }

    /**
     * @return list<string>
     */
    public static function sleepingPlaceTypes(): array
    {
        return array_map(fn (SleepingPlaceType $type): string => $type->value, SleepingPlaceType::cases());
    }

    /**
     * @return list<string>
     */
    public static function roomGenderPolicies(): array
    {
        return array_map(fn (GenderType $type): string => $type->value, GenderType::cases());
    }

    /**
     * @return array<string, string>
     */
    public static function requiredAmenityColumns(): array
    {
        return [
            'wifi' => 'require_wifi',
            'kitchen' => 'require_kitchen',
            'washing_machine' => 'require_washing_machine',
            'locker' => 'require_locker',
            'workspace' => 'require_workspace',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function excludedConditionColumns(): array
    {
        return [
            'smoking' => 'avoid_smoking',
            'pets' => 'avoid_pets',
            'mixed_room' => 'avoid_mixed_room',
        ];
    }

    /**
     * @return list<string>
     */
    public static function requiredAmenities(): array
    {
        return array_keys(self::requiredAmenityColumns());
    }

    /**
     * @return list<string>
     */
    public static function excludedConditions(): array
    {
        return array_keys(self::excludedConditionColumns());
    }
}
