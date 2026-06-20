<?php

namespace App\Services\SleepingPlaces;

use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCreationBatch;
use App\Models\User;
use App\Services\Domain\DomainOwnershipService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class SleepingPlaceBatchCreationService
{
    public function __construct(
        private readonly DomainOwnershipService $ownership,
        private readonly SleepingPlaceCreationService $sleepingPlaces,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, SleepingPlace>
     *
     * @throws AuthorizationException
     */
    public function createBatch(User $host, Room $room, array $data): Collection
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);
        $count = max(1, (int) ($data['places_count'] ?? 1));
        $template = (array) ($data['template_json'] ?? $data['template'] ?? []);

        SleepingPlaceCreationBatch::query()->create([
            'user_id' => $host->id,
            'property_id' => $room->property_id,
            'room_id' => $room->id,
            'batch_name' => $data['batch_name'] ?? null,
            'places_count' => $count,
            'template_json' => $template,
            'status' => 'created',
        ]);

        return $this->createIdenticalBeds($host, $room, $count, $template, recordBatch: false);
    }

    /**
     * @return Collection<int, SleepingPlace>
     *
     * @throws AuthorizationException
     */
    public function createBunkBeds(User $host, Room $room, int $pairs): Collection
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);
        $places = new Collection;

        foreach (range(1, max(1, $pairs)) as $pair) {
            $places->push($this->sleepingPlaces->create($host, $room, [
                'title' => 'Lower bunk '.$pair,
                'place_number' => (string) (($pair * 2) - 1),
                'place_type' => 'bottom_bunk',
                'bed_type' => 'single',
                'base_price' => 0,
                'currency' => 'EUR',
            ]));
            $places->push($this->sleepingPlaces->create($host, $room, [
                'title' => 'Upper bunk '.$pair,
                'place_number' => (string) ($pair * 2),
                'place_type' => 'top_bunk',
                'bed_type' => 'single',
                'base_price' => 0,
                'currency' => 'EUR',
            ]));
        }

        SleepingPlaceCreationBatch::query()->create([
            'user_id' => $host->id,
            'property_id' => $room->property_id,
            'room_id' => $room->id,
            'batch_name' => 'bunk_beds',
            'places_count' => $places->count(),
            'template_json' => ['type' => 'bunk_beds', 'pairs' => $pairs],
            'status' => 'created',
        ]);

        return $places;
    }

    /**
     * @param  array<string, mixed>  $template
     * @return Collection<int, SleepingPlace>
     *
     * @throws AuthorizationException
     */
    public function createIdenticalBeds(User $host, Room $room, int $count, array $template, bool $recordBatch = true): Collection
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);
        $places = new Collection;
        $existing = $room->sleepingPlaces()->count();

        foreach (range(1, max(1, $count)) as $index) {
            $number = (string) ($existing + $index);
            $places->push($this->sleepingPlaces->create($host, $room, array_merge([
                'title' => trim((string) ($template['title'] ?? 'Sleeping place')).' '.$number,
                'place_number' => $number,
                'place_type' => 'single_bed',
                'bed_type' => 'single',
                'currency' => 'EUR',
            ], $template)));
        }

        if ($recordBatch) {
            SleepingPlaceCreationBatch::query()->create([
                'user_id' => $host->id,
                'property_id' => $room->property_id,
                'room_id' => $room->id,
                'batch_name' => 'identical_beds',
                'places_count' => $places->count(),
                'template_json' => $template,
                'status' => 'created',
            ]);
        }

        return $places;
    }

    public function autoNumberPlaces(Room $room): void
    {
        $room->sleepingPlaces()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id'])
            ->each(fn (SleepingPlace $place, int $index) => $place->forceFill(['place_number' => (string) ($index + 1)])->save());
    }
}
