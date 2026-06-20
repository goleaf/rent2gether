<?php

namespace App\Services\Properties;

use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\User;
use Illuminate\Support\Collection;

class PropertyGuestSummaryService
{
    public function __construct(
        private readonly PropertyPrivacyService $privacy,
        private readonly PropertyLocationService $location,
        private readonly PropertyConditionService $condition,
        private readonly PropertyAccessService $access,
    ) {}

    /**
     * @return array{address:array{public:string},badges:list<string>,sections:list<array{key:string,title:string,open:bool,rows:list<array{label:string,value:string}>,items:list<array{label:string,value:string}>,warnings:list<string>}>}
     */
    public function build(Property $property, ?User $viewer = null): array
    {
        $property->loadMissing([
            'cityModel:id,name',
            'translations',
            'locationDetails',
            'conditionDetails',
            'accessDetails',
        ]);

        $translation = $this->translation($property->translations);

        return [
            'address' => [
                'public' => $this->privacy->publicAddress($property, $viewer),
            ],
            'badges' => $this->badges($property),
            'sections' => $this->visibleSections([
                [
                    'key' => 'main',
                    'title' => __('property.sections.main'),
                    'open' => true,
                    'rows' => $this->mainRows($property, $translation),
                    'warnings' => [],
                ],
                [
                    'key' => 'location',
                    'title' => __('property.sections.location'),
                    'open' => true,
                    'rows' => array_merge(
                        $this->location->getPublicLocationSummary($property),
                        $translation?->location_description ? [[
                            'label' => __('property.fields.location_description'),
                            'value' => $translation->location_description,
                        ]] : [],
                    ),
                    'warnings' => [],
                ],
                [
                    'key' => 'transport',
                    'title' => __('property.sections.transport'),
                    'open' => false,
                    'rows' => $this->location->getTransportSummary($property),
                    'warnings' => [],
                ],
                [
                    'key' => 'condition',
                    'title' => __('property.sections.condition'),
                    'open' => false,
                    'rows' => array_merge(
                        $this->condition->getConditionSummary($property),
                        $translation?->condition_description ? [[
                            'label' => __('property.fields.condition_description'),
                            'value' => $translation->condition_description,
                        ]] : [],
                    ),
                    'warnings' => $this->condition->getGuestWarnings($property),
                ],
                [
                    'key' => 'access',
                    'title' => __('property.sections.access'),
                    'open' => true,
                    'rows' => array_merge(
                        $this->access->getPublicAccessSummary($property),
                        $translation?->access_description ? [[
                            'label' => __('property.fields.access_description'),
                            'value' => $translation->access_description,
                        ]] : [],
                    ),
                    'warnings' => [],
                ],
                [
                    'key' => 'parking',
                    'title' => __('property.sections.parking'),
                    'open' => false,
                    'rows' => $this->location->getParkingSummary($property),
                    'warnings' => [],
                ],
                [
                    'key' => 'delivery',
                    'title' => __('property.sections.delivery'),
                    'open' => false,
                    'rows' => $this->deliveryRows($property, $translation),
                    'warnings' => [],
                ],
            ]),
        ];
    }

    /**
     * @return list<string>
     */
    private function badges(Property $property): array
    {
        return array_values(array_filter([
            $property->type?->label() ?: ($property->property_type?->label() ?? null),
            $property->rooms_count ? trans_choice('property.values.rooms_count', (int) $property->rooms_count, ['count' => (int) $property->rooms_count]) : null,
            $property->active_sleeping_places_count ? trans_choice('property.values.sleeping_places_count', (int) $property->active_sleeping_places_count, ['count' => (int) $property->active_sleeping_places_count]) : null,
            $property->floor !== null && $property->total_floors !== null
                ? __('property.values.floor_of', ['floor' => $property->floor, 'total' => $property->total_floors])
                : null,
            $property->has_elevator ? __('property.values.has_elevator') : __('property.values.no_elevator'),
        ]));
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function mainRows(Property $property, ?PropertyTranslation $translation): array
    {
        return $this->rows([
            'title' => $translation?->title ?: $property->title,
            'property_type' => $property->type?->label() ?: $property->property_type?->label(),
            'city' => $property->cityModel?->name ?: $property->city,
            'district' => $property->district,
            'floor' => $property->floor === null ? null : (string) $property->floor,
            'total_floors' => $property->total_floors === null ? null : (string) $property->total_floors,
            'has_elevator' => $property->has_elevator ? __('property.values.yes') : __('property.values.no'),
            'rooms_count' => $property->rooms_count === null ? null : (string) $property->rooms_count,
            'bathrooms_count' => $property->bathrooms_count === null ? null : (string) $property->bathrooms_count,
            'current_residents_count' => $property->current_residents_count === null ? null : (string) $property->current_residents_count,
            'free_sleeping_places_count' => $property->free_sleeping_places_count === null ? null : (string) $property->free_sleeping_places_count,
            'occupied_sleeping_places_count' => $property->occupied_sleeping_places_count === null ? null : (string) $property->occupied_sleeping_places_count,
        ]);
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function deliveryRows(Property $property, ?PropertyTranslation $translation): array
    {
        $property->loadMissing('accessDetails');
        $access = $property->accessDetails;

        if (! $access) {
            return [];
        }

        return $this->rows([
            'delivery_allowed' => $this->yesNo($access->delivery_allowed),
            'delivery_dropoff_location' => $access->delivery_dropoff_location,
            'courier_rules_text' => $translation?->courier_rules_text,
            'delivery_instructions' => $translation?->delivery_instructions,
        ]);
    }

    /**
     * @param  list<array{key:string,title:string,open:bool,rows:list<array{label:string,value:string}>,warnings:list<string>}>  $sections
     * @return list<array{key:string,title:string,open:bool,rows:list<array{label:string,value:string}>,items:list<array{label:string,value:string}>,warnings:list<string>}>
     */
    private function visibleSections(array $sections): array
    {
        $visible = [];

        foreach ($sections as $section) {
            if ($section['rows'] === [] && $section['warnings'] === []) {
                continue;
            }

            $visible[] = [
                ...$section,
                'items' => $section['rows'],
            ];
        }

        return $visible;
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
                'label' => __('property.fields.'.$field),
                'value' => $value,
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, PropertyTranslation>  $translations
     */
    private function translation(Collection $translations): ?PropertyTranslation
    {
        foreach ($this->locales() as $locale) {
            $translation = $translations->firstWhere('locale', $locale);

            if ($translation instanceof PropertyTranslation) {
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

    private function yesNo(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? __('property.values.yes') : __('property.values.no');
    }
}
