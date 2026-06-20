<?php

namespace App\Services\Rooms;

use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\User;
use Illuminate\Support\Collection;

class RoomGuestSummaryService
{
    public function __construct(
        private readonly RoomPrivacyService $privacy,
        private readonly RoomLayoutService $layout,
        private readonly RoomComfortService $comfort,
        private readonly RoomAccessService $access,
        private readonly RoomConditionService $condition,
        private readonly RoomOccupancyService $occupancy,
    ) {}

    /**
     * @return array{title:string,badges:list<string>,occupancy:array{count:int,summary:string},sections:list<array{key:string,title:string,open:bool,items:list<array{label:string,value:string}>,warnings:list<string>}>}
     */
    public function build(Room $room, ?User $viewer = null): array
    {
        $room->loadMissing([
            'property:id,host_user_id,user_id',
            'translations',
            'layoutDetails',
            'comfortDetails',
            'accessDetails',
            'conditionDetails',
        ]);

        $translation = $this->translation($room->translations);

        return [
            'title' => __('room.public.title'),
            'badges' => $this->badges($room),
            'occupancy' => $this->occupancy->getPrivacySafeOccupantSummary($room),
            'sections' => $this->visibleSections([
                [
                    'key' => 'main',
                    'title' => __('room.sections.main'),
                    'open' => true,
                    'items' => $this->mainRows($room, $translation, $viewer),
                    'warnings' => [],
                ],
                [
                    'key' => 'layout',
                    'title' => __('room.sections.layout'),
                    'open' => true,
                    'items' => $this->layout->getLayoutSummary($room),
                    'warnings' => [],
                ],
                [
                    'key' => 'comfort',
                    'title' => __('room.sections.comfort'),
                    'open' => true,
                    'items' => array_merge(
                        $this->comfort->getComfortSummary($room),
                        $translation?->work_study_instructions ? [[
                            'label' => __('room.fields.work_study_instructions'),
                            'value' => $translation->work_study_instructions,
                        ]] : [],
                    ),
                    'warnings' => $this->comfort->getGuestWarnings($room),
                ],
                [
                    'key' => 'access_storage',
                    'title' => __('room.sections.access_storage'),
                    'open' => true,
                    'items' => array_merge(
                        $this->access->getAccessStorageSummary($room),
                        $translation?->storage_instructions ? [[
                            'label' => __('room.fields.storage_instructions'),
                            'value' => $translation->storage_instructions,
                        ]] : [],
                    ),
                    'warnings' => [],
                ],
                [
                    'key' => 'condition',
                    'title' => __('room.sections.condition'),
                    'open' => false,
                    'items' => $this->condition->getConditionSummary($room),
                    'warnings' => $this->condition->getGuestWarnings($room),
                ],
                [
                    'key' => 'rules',
                    'title' => __('room.sections.rules'),
                    'open' => true,
                    'items' => $this->rulesRows($translation),
                    'warnings' => [],
                ],
            ]),
        ];
    }

    /**
     * @return list<string>
     */
    private function badges(Room $room): array
    {
        return array_values(array_filter([
            $room->room_type?->label() ?: $room->type?->label(),
            $room->gender_policy?->label(),
            $room->sleeping_places_count || $room->beds_count
                ? trans_choice('room.values.sleeping_places_count', (int) ($room->sleeping_places_count ?: $room->beds_count), ['count' => (int) ($room->sleeping_places_count ?: $room->beds_count)])
                : null,
            $room->free_sleeping_places_count || $room->available_places_count
                ? trans_choice('room.values.free_places_count', (int) ($room->free_sleeping_places_count ?: $room->available_places_count), ['count' => (int) ($room->free_sleeping_places_count ?: $room->available_places_count)])
                : null,
        ]));
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function mainRows(Room $room, ?RoomTranslation $translation, ?User $viewer): array
    {
        $canShowRoomNumber = $viewer instanceof User && $this->privacy->canShowRoomNumber($viewer, $room);

        return $this->rows([
            'title' => $translation?->title ?: $room->title,
            'short_description' => $translation?->short_description ?: $translation?->summary,
            'room_number' => $canShowRoomNumber ? $room->room_number : null,
            'room_type' => $room->room_type?->label() ?: $room->type?->label(),
            'living_format' => $room->living_format ? __('room.living_formats.'.$room->living_format) : null,
            'gender_policy' => $room->gender_policy?->label(),
            'sleeping_places_count' => $room->sleeping_places_count === null ? null : (string) $room->sleeping_places_count,
            'free_sleeping_places_count' => $room->free_sleeping_places_count === null ? null : (string) $room->free_sleeping_places_count,
            'occupied_sleeping_places_count' => $room->occupied_sleeping_places_count === null ? null : (string) $room->occupied_sleeping_places_count,
            'who_lives_nearby_text' => $translation?->who_lives_nearby_text,
        ]);
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function rulesRows(?RoomTranslation $translation): array
    {
        if (! $translation) {
            return [];
        }

        return $this->rows([
            'room_rules_text' => $translation->room_rules_text,
            'quiet_hours_text' => $translation->quiet_hours_text,
            'food_rules_text' => $translation->food_rules_text,
            'conflict_instructions' => $translation->conflict_instructions,
            'special_notes' => $translation->special_notes,
        ]);
    }

    /**
     * @param  list<array{key:string,title:string,open:bool,items:list<array{label:string,value:string}>,warnings:list<string>}>  $sections
     * @return list<array{key:string,title:string,open:bool,items:list<array{label:string,value:string}>,warnings:list<string>}>
     */
    private function visibleSections(array $sections): array
    {
        return array_values(array_filter(
            $sections,
            fn (array $section): bool => $section['items'] !== [] || $section['warnings'] !== [],
        ));
    }

    /**
     * @param  array<string, ?string>  $values
     * @return list<array{label:string,value:string}>
     */
    private function rows(array $values): array
    {
        $rows = [];

        foreach ($values as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $rows[] = [
                'label' => __('room.fields.'.$field),
                'value' => $value,
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, RoomTranslation>  $translations
     */
    private function translation(Collection $translations): ?RoomTranslation
    {
        foreach ($this->locales() as $locale) {
            $translation = $translations->firstWhere('locale', $locale);

            if ($translation instanceof RoomTranslation) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function locales(): array
    {
        return array_values(array_unique(array_filter([
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
            'en',
            'ru',
        ])));
    }
}
