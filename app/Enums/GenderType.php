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
            self::Male => __('app.gender.male_only'),
            self::Female => __('app.gender.female_only'),
            self::Mixed => __('app.gender.mixed'),
        };
    }
}
