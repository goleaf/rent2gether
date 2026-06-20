<?php

namespace App\Services\HostBulk;

use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\Calendar\SleepingPlaceCalendarBootstrapService;
use Illuminate\Support\Collection;

class HostBulkCloneService
{
    public function __construct(private readonly SleepingPlaceCalendarBootstrapService $calendarBootstrap) {}

    public function cloneRoom(Room $room, array $options = []): Room
    {
        $clone = $room->replicate(['created_at', 'updated_at', 'completed_at']);
        $clone->title = ($room->title ?: __('host_bulk.defaults.room_copy')).' '.__('host_bulk.defaults.copy_suffix');
        $clone->publication_status = 'draft';
        $clone->completed_at = null;
        $clone->push();

        foreach ($room->translations as $translation) {
            $copy = $translation->replicate(['created_at', 'updated_at']);
            $copy->room_id = $clone->id;
            $copy->save();
        }

        if (($options['copy_photos'] ?? false) === true) {
            foreach ($room->mediaItems as $media) {
                $copy = $media->replicate(['created_at', 'updated_at']);
                $copy->mediable_id = $clone->id;
                $copy->save();
            }
        }

        return $clone->refresh();
    }

    public function cloneSleepingPlace(SleepingPlace $place, array $options = []): SleepingPlace
    {
        $clone = $place->replicate(['created_at', 'updated_at', 'completed_at', 'published_at']);
        $clone->display_name = ($place->display_name ?: __('host_bulk.defaults.sleeping_place_copy')).' '.__('host_bulk.defaults.copy_suffix');
        $clone->publication_status = 'draft';
        $clone->status = 'draft';
        $clone->completed_at = null;
        $clone->published_at = null;

        if (($options['copy_price'] ?? false) !== true) {
            $clone->base_price_per_night = 0;
        }

        $clone->push();

        foreach ($place->translations as $translation) {
            $copy = $translation->replicate(['created_at', 'updated_at']);
            $copy->sleeping_place_id = $clone->id;
            $copy->save();
        }

        if (($options['copy_photos'] ?? false) === true) {
            foreach ($place->mediaItems as $media) {
                $copy = $media->replicate(['created_at', 'updated_at']);
                $copy->mediable_id = $clone->id;
                $copy->save();
            }
        }

        if (($options['copy_calendar'] ?? false) === true) {
            $this->copyCalendar($place, $clone);
        }

        if (
            $clone->calendarSettings()->doesntExist()
            || $clone->calendarDays()->doesntExist()
            || $clone->availabilityDays()->doesntExist()
        ) {
            $this->calendarBootstrap->bootstrap($clone);
        }

        return $clone->refresh();
    }

    public function createIdenticalPlaces(Room $room, int $count, array $template): Collection
    {
        $nextNumber = ((int) $room->sleepingPlaces()->max('place_number')) + 1;
        $created = collect();

        for ($index = 0; $index < $count; $index++) {
            $place = $room->sleepingPlaces()->create([
                'property_id' => $room->property_id,
                'place_number' => (string) ($nextNumber + $index),
                'display_name' => ($template['display_name'] ?? __('host_bulk.defaults.sleeping_place')).' '.($index + 1),
                'type' => $template['type'] ?? 'single',
                'sleeping_place_type' => $template['sleeping_place_type'] ?? $template['type'] ?? 'single',
                'status' => $template['status'] ?? 'draft',
                'base_price_per_night' => $template['base_price_per_night'] ?? 0,
                'currency' => $template['currency'] ?? 'EUR',
                'min_nights' => $template['min_nights'] ?? 1,
                'max_guests' => $template['max_guests'] ?? 1,
                'publication_status' => $template['publication_status'] ?? 'draft',
            ]);

            $this->calendarBootstrap->bootstrap($place);
            $created->push($place->refresh());
        }

        return $created;
    }

    private function copyCalendar(SleepingPlace $source, SleepingPlace $clone): void
    {
        if ($source->calendarSettings) {
            $settings = $source->calendarSettings->replicate(['created_at', 'updated_at']);
            $settings->sleeping_place_id = $clone->id;
            $settings->save();
        }

        foreach ($source->calendarRules as $rule) {
            $copy = $rule->replicate(['created_at', 'updated_at']);
            $copy->sleeping_place_id = $clone->id;
            $copy->save();
        }

        foreach ($source->calendarDays as $day) {
            $copy = $day->replicate(['created_at', 'updated_at']);
            $copy->sleeping_place_id = $clone->id;
            $copy->booking_id = null;
            $copy->save();
        }

        foreach ($source->availabilityDays as $day) {
            $copy = $day->replicate(['created_at', 'updated_at']);
            $copy->sleeping_place_id = $clone->id;
            $copy->booking_id = null;
            $copy->save();
        }
    }
}
