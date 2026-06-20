<?php

namespace App\Services\HostCalendar\Data;

use App\Models\HostCalendarEvent;

final readonly class HostCalendarEventData
{
    public function __construct(
        public int $id,
        public string $eventType,
        public string $eventDate,
        public string $titleKey,
        public ?string $eventStatus = null,
        public ?string $placeStatus = null,
        public ?string $guestDisplayName = null,
    ) {}

    public static function fromModel(HostCalendarEvent $event): self
    {
        return new self(
            id: $event->id,
            eventType: $event->event_type,
            eventDate: $event->event_date->toDateString(),
            titleKey: $event->title_key,
            eventStatus: $event->event_status,
            placeStatus: $event->place_status,
            guestDisplayName: $event->guest_display_name,
        );
    }
}
