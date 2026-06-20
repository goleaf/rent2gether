<?php

namespace App\Services\HostOccupants\Data;

final readonly class HostOccupantFilters
{
    public function __construct(
        public string $scope = 'all',
        public ?int $propertyId = null,
        public ?int $roomId = null,
        public ?int $sleepingPlaceId = null,
        public ?string $paymentStatus = null,
        public ?string $stayStatus = null,
        public bool $onlyNeedsAttention = false,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            scope: (string) ($data['scope'] ?? 'all'),
            propertyId: isset($data['property_id']) ? (int) $data['property_id'] : null,
            roomId: isset($data['room_id']) ? (int) $data['room_id'] : null,
            sleepingPlaceId: isset($data['sleeping_place_id']) ? (int) $data['sleeping_place_id'] : null,
            paymentStatus: $data['payment_status'] ?? null,
            stayStatus: $data['stay_status'] ?? null,
            onlyNeedsAttention: (bool) ($data['only_needs_attention'] ?? false),
        );
    }
}
