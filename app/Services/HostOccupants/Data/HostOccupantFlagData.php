<?php

namespace App\Services\HostOccupants\Data;

use App\Models\HostGuestStayFlag;

final readonly class HostOccupantFlagData
{
    public function __construct(
        public int $id,
        public string $flagKey,
        public string $severity,
        public string $messageKey,
        public string $status,
    ) {}

    public static function fromModel(HostGuestStayFlag $flag): self
    {
        return new self(
            id: $flag->id,
            flagKey: $flag->flag_key,
            severity: $flag->severity,
            messageKey: $flag->message_key,
            status: $flag->status,
        );
    }
}
