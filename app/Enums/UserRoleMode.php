<?php

namespace App\Enums;

enum UserRoleMode: string
{
    case Guest = 'guest';
    case Host = 'host';
    case GuestHost = 'guest_host';

    public function label(): string
    {
        return __('domain.role_modes.'.$this->value);
    }

    public function canHost(): bool
    {
        return $this === self::Host || $this === self::GuestHost;
    }

    public function canGuest(): bool
    {
        return $this === self::Guest || $this === self::GuestHost;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $mode): string => $mode->value, self::cases());
    }
}
