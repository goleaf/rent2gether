<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use Illuminate\Support\Collection;

class SleepingPlaceGuestSummaryService
{
    public function __construct(
        private readonly SleepingPlacePrivacyService $privacy,
        private readonly SleepingPlacePhysicalService $physical,
        private readonly SleepingPlaceComfortService $comfort,
        private readonly SleepingPlaceStorageService $storage,
        private readonly SleepingPlacePositionService $position,
        private readonly SleepingPlaceConditionService $condition,
    ) {}

    /**
     * @return array{title:string,badges:list<string>,sections:list<array{key:string,title:string,open:bool,items:list<array{label:string,value:string}>,warnings:list<string>}>}
     */
    public function build(SleepingPlace $place, ?User $viewer = null): array
    {
        $place->loadMissing([
            'property:id,host_user_id,user_id',
            'translations',
            'physicalDetails',
            'comfortDetails',
            'storageDetails',
            'positionDetails',
            'conditionDetails',
        ]);

        $translation = $this->translation($place->translations);

        return [
            'title' => __('sleeping_place.public.title'),
            'badges' => $this->badges($place),
            'sections' => $this->visibleSections([
                [
                    'key' => 'main',
                    'title' => __('sleeping_place.sections.main'),
                    'open' => true,
                    'items' => $this->mainRows($place, $translation, $viewer),
                    'warnings' => [],
                ],
                [
                    'key' => 'physical',
                    'title' => __('sleeping_place.sections.physical'),
                    'open' => true,
                    'items' => $this->physical->getPhysicalSummary($place),
                    'warnings' => $this->physical->getPhysicalWarnings($place),
                ],
                [
                    'key' => 'comfort',
                    'title' => __('sleeping_place.sections.comfort'),
                    'open' => true,
                    'items' => array_merge(
                        $this->comfort->getComfortSummary($place),
                        $translation?->what_is_included ? [[
                            'label' => __('sleeping_place.fields.what_is_included'),
                            'value' => $translation->what_is_included,
                        ]] : [],
                        $translation?->what_guest_should_bring ? [[
                            'label' => __('sleeping_place.fields.what_guest_should_bring'),
                            'value' => $translation->what_guest_should_bring,
                        ]] : [],
                    ),
                    'warnings' => $this->comfort->getSleepQualityWarnings($place),
                ],
                [
                    'key' => 'storage',
                    'title' => __('sleeping_place.sections.storage'),
                    'open' => true,
                    'items' => array_merge(
                        $this->storage->getStorageSummary($place),
                        $translation?->storage_instructions ? [[
                            'label' => __('sleeping_place.fields.storage_instructions'),
                            'value' => $translation->storage_instructions,
                        ]] : [],
                    ),
                    'warnings' => $this->storage->getStorageWarnings($place),
                ],
                [
                    'key' => 'position',
                    'title' => __('sleeping_place.sections.position'),
                    'open' => true,
                    'items' => $this->position->getPositionSummary($place),
                    'warnings' => $this->position->getNoiseAndPrivacyWarnings($place),
                ],
                [
                    'key' => 'condition',
                    'title' => __('sleeping_place.sections.condition'),
                    'open' => false,
                    'items' => $this->condition->getConditionSummary($place),
                    'warnings' => $this->condition->getRepairWarnings($place),
                ],
                [
                    'key' => 'safety',
                    'title' => __('sleeping_place.sections.safety'),
                    'open' => false,
                    'items' => $translation?->safety_notes ? [[
                        'label' => __('sleeping_place.fields.safety_notes'),
                        'value' => $translation->safety_notes,
                    ]] : [],
                    'warnings' => [],
                ],
            ]),
        ];
    }

    /**
     * @return list<string>
     */
    private function badges(SleepingPlace $place): array
    {
        return array_values(array_filter([
            $place->sleeping_place_type?->label() ?: $place->type?->label(),
            $place->bunk_level ? __('sleeping_place.bunk_levels.'.$place->bunk_level) : null,
            $place->max_guests > 1 ? trans_choice('sleeping_place.values.max_guests', (int) $place->max_guests, ['count' => (int) $place->max_guests]) : __('sleeping_place.values.one_person'),
            $place->instant_booking_enabled ? __('sleeping_place.values.instant_booking') : null,
        ]));
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function mainRows(SleepingPlace $place, ?SleepingPlaceTranslation $translation, ?User $viewer): array
    {
        $canShowInternalName = $viewer instanceof User && $this->privacy->canShowInternalName($viewer, $place);

        return $this->rows([
            'title' => $translation?->title ?: $place->display_name,
            'short_description' => $translation?->short_description ?: $translation?->summary,
            'place_number' => $place->place_number,
            'internal_name' => $canShowInternalName ? $place->internal_name : null,
            'sleeping_place_type' => $place->sleeping_place_type?->label() ?: $place->type?->label(),
            'sleeping_place_subtype' => $place->sleeping_place_subtype,
            'bunk_level' => $place->bunk_level ? __('sleeping_place.bunk_levels.'.$place->bunk_level) : null,
            'max_guests' => (string) $place->max_guests,
            'main_pros' => $translation?->main_pros ?: $translation?->sleeping_place_pros,
            'important_cons' => $translation?->important_cons ?: $translation?->sleeping_place_cons,
            'special_notes' => $translation?->special_notes ?: $translation?->sleeping_place_special_notes,
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
                'label' => __('sleeping_place.fields.'.$field),
                'value' => $value,
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, SleepingPlaceTranslation>  $translations
     */
    private function translation(Collection $translations): ?SleepingPlaceTranslation
    {
        foreach ($this->locales() as $locale) {
            $translation = $translations->firstWhere('locale', $locale);

            if ($translation instanceof SleepingPlaceTranslation) {
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
