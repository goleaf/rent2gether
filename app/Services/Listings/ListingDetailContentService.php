<?php

namespace App\Services\Listings;

use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListingDetailContentService
{
    public function __construct(
        private readonly ListingDetailPrivacyService $privacy,
        private readonly ListingDetailSectionService $sections,
    ) {}

    /**
     * @return array{sections:list<array{key:string,title_key:string,items:list<array{label_key:string,text:string}>,open_by_default:bool}>}
     */
    public function forSleepingPlace(SleepingPlace $place, ?User $viewer = null): array
    {
        $place->loadMissing([
            'translations',
            'room.translations',
            'property.translations',
        ]);

        $property = $place->property;
        $isHostViewer = $viewer instanceof User && $property?->host_user_id === $viewer->id;
        $propertyTranslations = $property?->translations ?? new Collection;
        $roomTranslations = $place->room?->translations ?? new Collection;
        $placeTranslations = $place->translations;

        return [
            'sections' => $this->sections->visibleSections([
                [
                    'key' => 'description',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.short_description', $this->field($propertyTranslations, 'short_description', ['summary'])),
                        $this->item('listing_detail.fields.sleeping_place_description', $this->field($placeTranslations, 'sleeping_place_description', ['description'])),
                        $this->item('listing_detail.fields.room_description', $this->field($roomTranslations, 'room_description', ['description'])),
                        $this->item('listing_detail.fields.full_description', $this->field($propertyTranslations, 'full_description', ['description'])),
                        $this->item('listing_detail.fields.why_convenient', $this->field($propertyTranslations, 'why_convenient', ['what_guests_like'])),
                        $this->item('listing_detail.fields.suitable_for', $this->field($propertyTranslations, 'suitable_for')),
                        $this->item('listing_detail.fields.not_suitable_for', $this->field($propertyTranslations, 'not_suitable_for')),
                        $this->item('listing_detail.fields.pros', $this->firstText([
                            $this->field($placeTranslations, 'sleeping_place_pros'),
                            $this->field($roomTranslations, 'room_pros'),
                            $this->field($propertyTranslations, 'main_pros'),
                        ])),
                        $this->item('listing_detail.fields.cons', $this->firstText([
                            $this->field($placeTranslations, 'sleeping_place_cons'),
                            $this->field($roomTranslations, 'room_cons'),
                            $this->field($propertyTranslations, 'important_cons'),
                        ])),
                        $this->item('listing_detail.fields.what_to_know', $this->field($propertyTranslations, 'what_to_know_beforehand', ['what_to_know'])),
                    ]),
                ],
                [
                    'key' => 'included',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.what_is_included', $this->field($propertyTranslations, 'what_is_included')),
                        $this->item('listing_detail.fields.included_for_place', $this->field($placeTranslations, 'what_is_included_for_place')),
                        $this->item('listing_detail.fields.what_is_not_included', $this->field($propertyTranslations, 'what_is_not_included')),
                    ]),
                ],
                [
                    'key' => 'what_to_bring',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.what_to_bring', $this->field($propertyTranslations, 'what_to_bring')),
                        $this->item('listing_detail.fields.bring_for_place', $this->field($placeTranslations, 'what_guest_should_bring_for_place')),
                    ]),
                ],
                [
                    'key' => 'storage',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.belongings_storage', $this->field($propertyTranslations, 'where_to_store_belongings')),
                        $this->item('listing_detail.fields.room_storage', $this->field($roomTranslations, 'storage_instructions')),
                        $this->item('listing_detail.fields.shared_space', $this->field($roomTranslations, 'shared_space_instructions')),
                    ]),
                ],
                [
                    'key' => 'food_storage',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.food_storage', $this->field($propertyTranslations, 'where_to_store_food')),
                    ]),
                ],
                [
                    'key' => 'kitchen',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.kitchen', $this->field($propertyTranslations, 'kitchen_instructions')),
                    ]),
                ],
                [
                    'key' => 'bathroom',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.bathroom', $this->field($propertyTranslations, 'bathroom_instructions')),
                    ]),
                ],
                [
                    'key' => 'laundry',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.laundry', $this->field($propertyTranslations, 'laundry_instructions')),
                    ]),
                ],
                [
                    'key' => 'keys',
                    'items' => $this->keyItems($place, $viewer, $propertyTranslations),
                ],
                [
                    'key' => 'host_contact',
                    'items' => $this->hostContactItems($place, $viewer, $propertyTranslations),
                ],
                [
                    'key' => 'problem',
                    'items' => $this->items([
                        $this->item('listing_detail.fields.problem', $this->field($propertyTranslations, 'problem_instructions')),
                        $this->item('listing_detail.fields.lost_key', $this->field($propertyTranslations, 'lost_key_instructions')),
                        $this->item('listing_detail.fields.neighbor_conflict', $this->field($propertyTranslations, 'neighbor_conflict_instructions')),
                        $this->item('listing_detail.fields.repair_problem', $this->field($propertyTranslations, 'repair_problem_instructions')),
                    ]),
                ],
            ], $isHostViewer),
        ];
    }

    /**
     * @param  Collection<int, mixed>  $translations
     * @param  list<string>  $aliases
     */
    private function field(Collection $translations, string $field, array $aliases = []): ?string
    {
        $fields = array_values(array_unique([$field, ...$aliases]));

        foreach ($this->locales() as $locale) {
            $translation = $translations->firstWhere('locale', $locale);

            if (! $translation) {
                continue;
            }

            foreach ($fields as $candidate) {
                $value = $translation->{$candidate} ?? null;

                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }
            }
        }

        return null;
    }

    /**
     * @param  list<?string>  $values
     */
    private function firstText(array $values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * @return list<array{label_key:string,text:?string}>
     */
    private function keyItems(SleepingPlace $place, ?User $viewer, Collection $propertyTranslations): array
    {
        $hasSensitiveEntryContent = $this->field($propertyTranslations, 'key_pickup_instructions') !== null
            || $this->field($propertyTranslations, 'night_entry_instructions') !== null;

        $items = [];

        if ($this->privacy->canShowEntryInstructions($place->property, $viewer)) {
            $items[] = $this->item('listing_detail.fields.key_pickup', $this->field($propertyTranslations, 'key_pickup_instructions'));
            $items[] = $this->item('listing_detail.fields.night_entry', $this->field($propertyTranslations, 'night_entry_instructions'));
        } elseif ($hasSensitiveEntryContent) {
            $items[] = $this->item('listing_detail.fields.key_pickup', __('listing_detail.hints.address_after_booking'));
        }

        $items[] = $this->item('listing_detail.fields.lost_key', $this->field($propertyTranslations, 'lost_key_instructions'));

        return $this->items($items);
    }

    /**
     * @return list<array{label_key:string,text:?string}>
     */
    private function hostContactItems(SleepingPlace $place, ?User $viewer, Collection $propertyTranslations): array
    {
        $hostContact = $this->field($propertyTranslations, 'host_contact_instructions');

        if ($hostContact === null) {
            return [];
        }

        if ($this->privacy->canShowHostPrivateContact($place->property, $viewer)) {
            return [$this->item('listing_detail.fields.host_contact', $hostContact)];
        }

        return [$this->item('listing_detail.fields.host_contact', __('listing_detail.hints.contact_after_booking'))];
    }

    /**
     * @param  list<array{label_key:string,text:?string}>  $items
     * @return list<array{label_key:string,text:?string}>
     */
    private function items(array $items): array
    {
        return array_values($items);
    }

    /**
     * @return array{label_key:string,text:?string}
     */
    private function item(string $labelKey, ?string $text): array
    {
        return [
            'label_key' => $labelKey,
            'text' => $text,
        ];
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
