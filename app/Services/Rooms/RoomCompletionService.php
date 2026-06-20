<?php

namespace App\Services\Rooms;

use App\Models\Room;

class RoomCompletionService
{
    /**
     * @return list<array{key:string,label:string,complete:bool}>
     */
    public function items(Room $room): array
    {
        $room->loadMissing(['translations', 'layoutDetails', 'comfortDetails', 'accessDetails', 'conditionDetails']);
        $translation = $room->translations->firstWhere('locale', app()->getLocale())
            ?: $room->translations->firstWhere('locale', config('app.fallback_locale', 'en'))
            ?: $room->translations->first();

        return [
            ['key' => 'title', 'label' => __('room.completion.items.title'), 'complete' => filled($translation?->title ?? $room->title)],
            ['key' => 'type', 'label' => __('room.completion.items.type'), 'complete' => filled($room->room_type ?? $room->type)],
            ['key' => 'living_format', 'label' => __('room.completion.items.living_format'), 'complete' => filled($room->living_format ?? $room->gender_policy)],
            ['key' => 'occupancy', 'label' => __('room.completion.items.occupancy'), 'complete' => (int) ($room->sleeping_places_count ?: $room->beds_count) > 0],
            ['key' => 'layout', 'label' => __('room.completion.items.layout'), 'complete' => $room->layoutDetails !== null],
            ['key' => 'comfort', 'label' => __('room.completion.items.comfort'), 'complete' => $room->comfortDetails !== null],
            ['key' => 'access_storage', 'label' => __('room.completion.items.access_storage'), 'complete' => $room->accessDetails !== null],
            ['key' => 'condition', 'label' => __('room.completion.items.condition'), 'complete' => $room->conditionDetails !== null],
            ['key' => 'rules', 'label' => __('room.completion.items.rules'), 'complete' => filled($translation?->room_rules_text ?? $room->room_rules_text)],
            ['key' => 'translations', 'label' => __('room.completion.items.translations'), 'complete' => $room->translations->whereIn('locale', ['en', 'ru'])->count() >= 2],
        ];
    }

    public function percentage(Room $room): int
    {
        $items = $this->items($room);
        $complete = count(array_filter($items, fn (array $item): bool => $item['complete']));

        return (int) round(($complete / max(1, count($items))) * 100);
    }
}
