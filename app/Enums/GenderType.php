<?php

namespace App\Enums;

enum GenderType: string
{
    case Male = 'male';
    case Female = 'female';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Male => __('listing.gender.male_only'),
            self::Female => __('listing.gender.female_only'),
            self::Mixed => __('listing.gender.mixed'),
        };
    }
}
