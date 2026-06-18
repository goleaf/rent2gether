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
            self::Male => 'Male only',
            self::Female => 'Female only',
            self::Mixed => 'Mixed',
        };
    }
}
