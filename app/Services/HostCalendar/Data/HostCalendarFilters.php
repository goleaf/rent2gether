<?php

namespace App\Services\HostCalendar\Data;

final readonly class HostCalendarFilters
{
    /**
     * @param  list<string>  $eventTypes
     */
    public function __construct(
        public ?int $propertyId = null,
        public ?int $roomId = null,
        public ?int $sleepingPlaceId = null,
        public array $eventTypes = [],
        public ?string $eventStatus = null,
        public ?string $view = null,
        public bool $onlyProblems = false,
        public ?string $payoutStatus = null,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            propertyId: isset($data['property_id']) ? (int) $data['property_id'] : null,
            roomId: isset($data['room_id']) ? (int) $data['room_id'] : null,
            sleepingPlaceId: isset($data['sleeping_place_id']) ? (int) $data['sleeping_place_id'] : null,
            eventTypes: array_values(array_filter((array) ($data['event_types'] ?? []))),
            eventStatus: $data['event_status'] ?? null,
            view: $data['view'] ?? null,
            onlyProblems: (bool) ($data['only_problems'] ?? false),
            payoutStatus: $data['payout_status'] ?? null,
        );
    }
}
