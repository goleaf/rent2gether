<?php

namespace App\Services\HostOccupants\Data;

final readonly class HostOccupantActionResultData
{
    public function __construct(
        public string $status,
        public string $messageKey,
        public ?int $bookingId = null,
        public ?int $resourceId = null,
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message_key' => $this->messageKey,
            'booking_id' => $this->bookingId,
            'resource_id' => $this->resourceId,
        ];
    }
}
