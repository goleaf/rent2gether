<?php

namespace App\Services\HostListings\Wizard;

use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\Property;

class HostListingStatusService
{
    public function markPublished(Property $property): Property
    {
        $property->forceFill([
            'status' => PropertyStatus::Active->value,
            'publication_status' => 'published',
            'review_status' => 'auto_approved',
            'reviewed_at' => now(),
            'published_at' => now(),
            'paused_at' => null,
            'archived_at' => null,
        ])->save();

        $property->rooms()->update([
            'status' => RoomStatus::Active->value,
            'publication_status' => 'published',
            'completed_at' => now(),
        ]);

        $property->sleepingPlaces()->update([
            'status' => SleepingPlaceStatus::Active->value,
            'publication_status' => 'published',
            'completed_at' => now(),
            'published_at' => now(),
        ]);

        return $property->refresh();
    }

    public function markIncomplete(Property $property): Property
    {
        $property->forceFill(['publication_status' => 'incomplete'])->save();

        return $property->refresh();
    }
}
