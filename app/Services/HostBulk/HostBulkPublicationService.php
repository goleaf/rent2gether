<?php

namespace App\Services\HostBulk;

use App\Enums\SleepingPlaceStatus;
use App\Models\SleepingPlace;
use Illuminate\Support\Collection;

class HostBulkPublicationService
{
    public function hideListings(Collection $places): array
    {
        return $this->setPublicationStatus($places, 'hidden', SleepingPlaceStatus::Hidden->value);
    }

    public function activateListings(Collection $places): array
    {
        $affected = 0;
        $skipped = 0;

        foreach ($places as $place) {
            if (! $place instanceof SleepingPlace || ! $this->canActivate($place)) {
                $skipped++;

                continue;
            }

            $place->forceFill([
                'status' => SleepingPlaceStatus::Active->value,
                'publication_status' => 'published',
            ])->save();
            $affected++;
        }

        return $this->result($places->count(), $affected, $skipped);
    }

    public function pauseListings(Collection $places): array
    {
        return $this->setPublicationStatus($places, 'paused', SleepingPlaceStatus::Unavailable->value);
    }

    public function archiveListings(Collection $places): array
    {
        return $this->setPublicationStatus($places, 'archived', SleepingPlaceStatus::Closed->value);
    }

    public function publishListings(Collection $places): array
    {
        return $this->activateListings($places);
    }

    private function setPublicationStatus(Collection $places, string $publicationStatus, string $status): array
    {
        $affected = 0;

        foreach ($places as $place) {
            if ($place instanceof SleepingPlace) {
                $place->forceFill([
                    'status' => $status,
                    'publication_status' => $publicationStatus,
                ])->save();
                $affected++;
            }
        }

        return $this->result($places->count(), $affected, max(0, $places->count() - $affected));
    }

    private function canActivate(SleepingPlace $place): bool
    {
        return in_array($place->publication_status, ['published', 'hidden', 'paused'], true)
            && (float) $place->base_price_per_night > 0;
    }

    private function result(int $selected, int $affected, int $skipped): array
    {
        return [
            'selected_count' => $selected,
            'affected_count' => $affected,
            'skipped_count' => $skipped,
            'failed_count' => 0,
        ];
    }
}
