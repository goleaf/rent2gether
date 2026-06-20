<?php

namespace App\Livewire\Host;

use App\Actions\Media\DeleteMediaItemAction;
use App\Actions\Media\StoreMediaItemAction;
use App\Enums\PropertyRentalUnitType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Livewire\Concerns\BuildsLocalizedMediaCaptions;
use App\Livewire\Concerns\ManagesLocalizedFormTranslations;
use App\Models\City;
use App\Models\Country;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Region;
use App\Services\Catalog\AmenityRuleLookupService;
use App\Services\Geo\GeoSearchService;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class PropertyForm extends Component
{
    use BuildsLocalizedMediaCaptions;
    use ManagesLocalizedFormTranslations;
    use WithFileUploads;

    private const STEP_COUNT = 9;

    private const PHOTO_FIELDS = [
        'entrancePhoto' => 'entrance',
        'hallwayPhoto' => 'hallway',
        'kitchenPhoto' => 'kitchen',
        'bathroomPhoto' => 'bathroom',
        'commonAreaPhoto' => 'common_area',
    ];

    private const TRANSLATION_FIELDS = [
        'title',
        'summary',
        'description',
        'what_to_know',
        'suitable_for',
        'not_suitable_for',
    ];

    public ?int $propertyId = null;

    public int $step = 1;

    public bool $wasSaved = false;

    public string $rentalUnitType = '';

    public string $propertyType = '';

    public string $countryQuery = '';

    public ?int $countryId = null;

    public bool $countrySearchOpen = false;

    public string $cityQuery = '';

    public ?int $cityId = null;

    public bool $citySearchOpen = false;

    public string $regionName = '';

    public string $district = '';

    public string $street = '';

    public string $houseNumber = '';

    public string $apartmentNumber = '';

    public ?int $floor = null;

    public ?int $totalFloors = null;

    public bool $hasElevator = false;

    public bool $useApproximatePublicLocation = true;

    public bool $hideExactAddressUntilBooking = true;

    public ?float $totalArea = null;

    public ?int $roomsCount = null;

    public ?int $bathroomsCount = null;

    public ?int $showersCount = null;

    public ?int $kitchensCount = null;

    public ?int $balconiesCount = null;

    public ?int $maxGuests = null;

    public string $repairState = '';

    public string $noiseLevel = '';

    public string $cleanlinessLevel = '';

    public string $safetyLevel = '';

    /** @var list<int> */
    public array $amenityIds = [];

    /** @var list<int> */
    public array $ruleIds = [];

    public $entrancePhoto = null;

    public $hallwayPhoto = null;

    public $kitchenPhoto = null;

    public $bathroomPhoto = null;

    public $commonAreaPhoto = null;

    public function mount(?Property $property = null): void
    {
        $this->fillBlankTranslations(self::TRANSLATION_FIELDS);

        if (! $property?->exists) {
            return;
        }

        abort_unless($property->isOwnedBy(auth()->user()), 403);

        $property->load([
            'translations',
            'amenities:id',
            'rules:id',
            'countryModel' => fn ($query) => $query
                ->select(['id', 'iso2', 'code', 'name'])
                ->translated(app()->getLocale()),
            'cityModel' => fn ($query) => $query
                ->select(['id', 'name'])
                ->translated(app()->getLocale()),
            'region:id,name',
        ]);

        $this->propertyId = $property->id;
        $this->rentalUnitType = $property->rental_unit_type?->value ?? '';
        $this->propertyType = $property->type?->value ?? '';
        $this->countryId = $property->country_id;
        $this->countryQuery = $property->countryModel?->localizedName() ?: $property->country ?: '';
        $this->cityId = $property->city_id;
        $this->cityQuery = $property->cityModel?->localizedName() ?: $property->city ?: '';
        $this->regionName = $property->region_name ?: $property->region?->name ?: '';
        $this->district = $property->district ?: '';
        $this->street = $property->street ?: $property->address_line_1 ?: '';
        $this->houseNumber = $property->house_number ?: $property->building ?: '';
        $this->apartmentNumber = $property->apartment_number ?: $property->apartment ?: '';
        $this->floor = $property->floor;
        $this->totalFloors = $property->total_floors;
        $this->hasElevator = (bool) $property->has_elevator;
        $this->useApproximatePublicLocation = (bool) ($property->approximate_latitude || $property->approximate_longitude);
        $this->hideExactAddressUntilBooking = ! (bool) $property->show_exact_address_before_booking;
        $this->totalArea = $property->total_area === null ? null : (float) $property->total_area;
        $this->roomsCount = $property->rooms_count;
        $this->bathroomsCount = $property->bathrooms_count;
        $this->showersCount = $property->showers_count;
        $this->kitchensCount = $property->kitchens_count;
        $this->balconiesCount = $property->balconies_count;
        $this->maxGuests = $property->max_guests;
        $this->repairState = $property->repair_state ?: '';
        $this->noiseLevel = $property->noise_level ?: '';
        $this->cleanlinessLevel = $property->cleanliness_level ?: '';
        $this->safetyLevel = $property->safety_level ?: '';
        $this->amenityIds = $property->getRelation('amenities')->pluck('id')->map(fn (int $id): int => $id)->all();
        $this->ruleIds = $property->getRelation('rules')->pluck('id')->map(fn (int $id): int => $id)->all();

        $this->loadLocalizedTranslations($property->translations, self::TRANSLATION_FIELDS);
    }

    public function updatedCountryQuery(): void
    {
        $this->countryId = null;
        $this->cityId = null;
        $this->cityQuery = '';
        $this->countrySearchOpen = true;
    }

    public function updatedCityQuery(): void
    {
        $this->cityId = null;
        $this->citySearchOpen = true;
    }

    public function selectCountry(int $countryId): void
    {
        $country = Country::query()
            ->select(['id', 'iso2', 'code', 'name', 'status', 'is_active'])
            ->visible()
            ->translated(app()->getLocale())
            ->find($countryId);

        if (! $country) {
            return;
        }

        $this->countryId = $country->id;
        $this->countryQuery = $country->localizedName();
        $this->countrySearchOpen = false;
        $this->cityId = null;
        $this->cityQuery = '';
    }

    public function selectCity(int $cityId): void
    {
        $city = City::query()
            ->select(['id', 'country_id', 'region_id', 'name', 'status', 'is_active'])
            ->with(['region:id,name'])
            ->visible()
            ->translated(app()->getLocale())
            ->when($this->countryId, fn (Builder $query): Builder => $query->where('country_id', $this->countryId))
            ->find($cityId);

        if (! $city) {
            return;
        }

        $this->cityId = $city->id;
        $this->cityQuery = $city->localizedName();
        $this->citySearchOpen = false;

        if (! $this->regionName && $city->region) {
            $this->regionName = $city->region->name;
        }
    }

    public function nextStep(): void
    {
        $this->saveStep();
        $this->step = min(self::STEP_COUNT, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->wasSaved = false;
    }

    public function saveStep(): void
    {
        $this->validate($this->rulesForStep($this->step), attributes: $this->validationAttributes());

        $property = $this->persistProperty($this->draftStatus());
        $this->syncStepRelations($property, $this->step);
        $this->wasSaved = true;
    }

    public function publish()
    {
        $this->validate($this->allRules(), attributes: $this->validationAttributes());

        $property = $this->persistProperty(PropertyStatus::Active->value);
        $this->syncAmenities($property);
        $this->syncRules($property);
        $this->syncPhotos($property);

        session()->flash('success', __('notifications.flash.property_created'));

        return $this->redirect(route('host.properties.show', [
            'locale' => app()->getLocale(),
            'property' => $property,
        ]), navigate: true);
    }

    /**
     * @return array<string, array{url:string,caption:string}>
     */
    #[Computed]
    public function savedPhotoPreviews(): array
    {
        $property = $this->propertyModel();

        if (! $property) {
            return [];
        }

        return $property->mediaItems()
            ->select([
                'id',
                'mediable_type',
                'mediable_id',
                'collection',
                'disk',
                'path',
                'thumbnail_path',
                'thumb_path',
                'mobile_path',
                'full_path',
                'alt_text',
                'sort_order',
                'is_primary',
                'is_cover',
                'status',
            ])
            ->with(['translations:id,media_item_id,locale,caption'])
            ->whereIn('collection', array_values(self::PHOTO_FIELDS))
            ->active()
            ->orderByDesc('is_primary')
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->unique('collection')
            ->mapWithKeys(fn (MediaItem $item): array => [
                (string) $item->collection => [
                    'url' => $item->imageUrl('thumb'),
                    'caption' => $item->localizedCaption() ?: __('media.default_caption'),
                ],
            ])
            ->all();
    }

    /**
     * @return list<array{number:int,title:string}>
     */
    public function wizardSteps(): array
    {
        return collect(range(1, self::STEP_COUNT))
            ->map(fn (int $step): array => [
                'number' => $step,
                'title' => __('host.property_wizard.steps.'.$step.'.title'),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function rentalUnitTypeOptions(): array
    {
        return collect(PropertyRentalUnitType::cases())
            ->mapWithKeys(fn (PropertyRentalUnitType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function propertyTypeOptions(): array
    {
        return collect(PropertyType::cases())
            ->mapWithKeys(fn (PropertyType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function levelOptions(string $group): array
    {
        return collect(['low', 'moderate', 'good', 'high'])
            ->mapWithKeys(fn (string $value): array => [$value => __('host.property_wizard.options.'.$group.'.'.$value)])
            ->all();
    }

    #[Computed]
    public function countryResults(): array
    {
        if (! $this->countrySearchOpen || Str::length(GeoNameNormalizer::normalize($this->countryQuery)) < 2) {
            return [];
        }

        return app(GeoSearchService::class)
            ->countries($this->countryQuery, app()->getLocale())
            ->map(fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->localizedName(),
                'code' => $country->iso2 ?: $country->code,
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function cityResults(): array
    {
        if (! $this->citySearchOpen || Str::length(GeoNameNormalizer::normalize($this->cityQuery)) < 2) {
            return [];
        }

        return app(GeoSearchService::class)
            ->cities($this->cityQuery, app()->getLocale(), countryId: $this->countryId)
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->localizedName(),
                'country' => $city->country?->localizedName(),
                'region' => $city->region?->name,
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function amenityOptions(): array
    {
        return app(AmenityRuleLookupService::class)->amenityOptions(
            locale: app()->getLocale(),
            categories: ['property', 'kitchen', 'bathroom', 'safety', 'long_stay', 'accessibility', 'transport', 'storage', 'work_study'],
        );
    }

    #[Computed]
    public function ruleOptions(): array
    {
        return app(AmenityRuleLookupService::class)->ruleOptions(locale: app()->getLocale());
    }

    public function render(): View
    {
        return view('livewire.host.property-form')
            ->layout('layouts.app', ['title' => __('host.property_wizard.title')]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'rentalUnitType' => ['required', ValidationRule::enum(PropertyRentalUnitType::class)],
            ],
            2 => [
                'propertyType' => ['required', ValidationRule::enum(PropertyType::class)],
            ],
            3 => [
                'countryId' => ['required', 'integer', 'exists:countries,id'],
                'cityId' => ['required', 'integer', 'exists:cities,id'],
                'regionName' => ['nullable', 'string', 'max:120'],
                'district' => ['nullable', 'string', 'max:120'],
                'street' => ['nullable', 'string', 'max:200'],
                'houseNumber' => ['nullable', 'string', 'max:50'],
                'apartmentNumber' => ['nullable', 'string', 'max:50'],
                'floor' => ['nullable', 'integer', 'min:0', 'max:200'],
                'totalFloors' => ['nullable', 'integer', 'min:0', 'max:200'],
                'hasElevator' => ['boolean'],
                'useApproximatePublicLocation' => ['boolean'],
                'hideExactAddressUntilBooking' => ['boolean'],
            ],
            4 => [
                'totalArea' => ['nullable', 'numeric', 'min:1', 'max:100000'],
                'roomsCount' => ['nullable', 'integer', 'min:0', 'max:200'],
                'bathroomsCount' => ['nullable', 'integer', 'min:0', 'max:100'],
                'showersCount' => ['nullable', 'integer', 'min:0', 'max:100'],
                'kitchensCount' => ['nullable', 'integer', 'min:0', 'max:100'],
                'balconiesCount' => ['nullable', 'integer', 'min:0', 'max:100'],
                'maxGuests' => ['nullable', 'integer', 'min:1', 'max:1000'],
                'repairState' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
                'noiseLevel' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
                'cleanlinessLevel' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
                'safetyLevel' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
            ],
            5 => $this->localizedTranslationRules([
                'title' => ['required', 'string', 'max:255'],
                'summary' => ['nullable', 'string', 'max:500'],
                'description' => ['nullable', 'string', 'max:4000'],
                'what_to_know' => ['nullable', 'string', 'max:1000'],
                'suitable_for' => ['nullable', 'string', 'max:1000'],
                'not_suitable_for' => ['nullable', 'string', 'max:1000'],
            ]),
            6 => [
                'amenityIds' => ['array'],
                'amenityIds.*' => ['integer', 'exists:amenities,id'],
            ],
            7 => [
                'ruleIds' => ['array'],
                'ruleIds.*' => ['integer', 'exists:rules,id'],
            ],
            8 => $this->photoRules(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function allRules(): array
    {
        return collect(range(1, 8))
            ->flatMap(fn (int $step): array => $this->rulesForStep($step))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function photoRules(): array
    {
        return collect(array_keys(self::PHOTO_FIELDS))
            ->mapWithKeys(fn (string $field): array => [
                $field => ['nullable', 'image', 'max:2048', 'dimensions:max_width=2400,max_height=2400'],
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('host.property_wizard.validation_attributes');

        return array_merge(
            is_array($attributes) ? $attributes : [],
            $this->localizedValidationAttributes('host.property_wizard.translation_fields', self::TRANSLATION_FIELDS),
        );
    }

    private function persistProperty(string $status): Property
    {
        $property = $this->propertyModel();
        $country = $this->countryId ? Country::query()->find($this->countryId) : null;
        $city = $this->cityId ? City::query()->with('region')->find($this->cityId) : null;
        $region = $this->regionModel($country, $city);
        $fallbackTitle = $this->firstTranslationValue('title') ?: $property?->title ?: __('host.property_wizard.draft_title');
        $countryName = $country?->localizedName() ?: $this->countryQuery ?: $property?->country ?: '';
        $cityName = $city?->localizedName() ?: $this->cityQuery ?: $property?->city ?: '';
        $approximateLatitude = $this->useApproximatePublicLocation ? ($city?->latitude ?: $property?->approximate_latitude) : null;
        $approximateLongitude = $this->useApproximatePublicLocation ? ($city?->longitude ?: $property?->approximate_longitude) : null;

        $data = [
            'user_id' => auth()->id(),
            'host_user_id' => auth()->id(),
            'rental_unit_type' => $this->rentalUnitType ?: PropertyRentalUnitType::SleepingPlace->value,
            'type' => $this->propertyType ?: PropertyType::Apartment->value,
            'title' => $fallbackTitle,
            'description' => $this->firstTranslationValue('description') ?: $property?->description,
            'country_id' => $country?->id,
            'region_id' => $region?->id,
            'region_name' => $this->regionName ?: $region?->name,
            'city_id' => $city?->id,
            'country' => $countryName,
            'city' => $cityName,
            'district' => $this->district ?: null,
            'street' => $this->street ?: null,
            'building' => $this->houseNumber ?: null,
            'apartment' => $this->apartmentNumber ?: null,
            'floor' => $this->floor,
            'has_elevator' => $this->hasElevator,
            'show_exact_address' => ! $this->hideExactAddressUntilBooking,
            'address_line_1' => $this->street ?: null,
            'house_number' => $this->houseNumber ?: null,
            'apartment_number' => $this->apartmentNumber ?: null,
            'total_floors' => $this->totalFloors,
            'latitude' => null,
            'longitude' => null,
            'lat' => null,
            'lng' => null,
            'approximate_latitude' => $approximateLatitude,
            'approximate_longitude' => $approximateLongitude,
            'show_exact_address_before_booking' => ! $this->hideExactAddressUntilBooking,
            'show_exact_address_after_payment' => true,
            'total_area' => $this->totalArea,
            'rooms_count' => $this->roomsCount ?: 0,
            'bathrooms_count' => $this->bathroomsCount ?: 0,
            'showers_count' => $this->showersCount ?: 0,
            'kitchens_count' => $this->kitchensCount ?: 0,
            'balconies_count' => $this->balconiesCount ?: 0,
            'max_guests' => $this->maxGuests ?: 1,
            'noise_level' => $this->noiseLevel ?: null,
            'cleanliness_level' => $this->cleanlinessLevel ?: null,
            'safety_level' => $this->safetyLevel ?: null,
            'repair_state' => $this->repairState ?: null,
            'status' => $status,
        ];

        if ($property) {
            $property->update($data);
        } else {
            $property = Property::query()->create($data);
            $this->propertyId = $property->id;
        }

        $this->syncTranslations($property);

        return $property->refresh();
    }

    private function syncStepRelations(Property $property, int $step): void
    {
        if ($step >= 6) {
            $this->syncAmenities($property);
        }

        if ($step >= 7) {
            $this->syncRules($property);
        }

        if ($step >= 8) {
            $this->syncPhotos($property);
        }
    }

    private function syncAmenities(Property $property): void
    {
        $property->amenities()->sync($this->integerIds($this->amenityIds));
    }

    private function syncRules(Property $property): void
    {
        $property->rules()->sync($this->integerIds($this->ruleIds));
    }

    private function syncTranslations(Property $property): void
    {
        foreach ($this->contentLocales() as $localeData) {
            $locale = $localeData['code'];
            $translation = $this->translations[$locale] ?? [];
            $title = trim((string) ($translation['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $property->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $title,
                    'summary' => $this->blankToNull($translation['summary']),
                    'description' => $this->blankToNull($translation['description']),
                    'what_to_know' => $this->blankToNull($translation['what_to_know']),
                    'suitable_for' => $this->blankToNull($translation['suitable_for']),
                    'not_suitable_for' => $this->blankToNull($translation['not_suitable_for']),
                ],
            );
        }
    }

    private function syncPhotos(Property $property): void
    {
        $store = app(StoreMediaItemAction::class);
        $delete = app(DeleteMediaItemAction::class);

        foreach (self::PHOTO_FIELDS as $field => $collection) {
            $photo = $this->{$field};

            if (! $photo instanceof UploadedFile) {
                continue;
            }

            $property->mediaItems()
                ->where('collection', $collection)
                ->get()
                ->each(function ($media) use ($delete): void {
                    $delete->handle($media);
                });

            $store->handle(
                owner: $property,
                file: $photo,
                user: auth()->user(),
                collection: $collection,
                captions: $this->localizedCaptions('host.property_wizard.photos.'.$collection),
                makePrimary: $collection === 'entrance',
            );

            $this->{$field} = null;
        }
    }

    private function regionModel(?Country $country, ?City $city): ?Region
    {
        if ($city?->region) {
            return $city->region;
        }

        if (! $country || $this->regionName === '') {
            return null;
        }

        return Region::query()
            ->where('country_id', $country->id)
            ->where('name_normalized', GeoNameNormalizer::normalize($this->regionName))
            ->first();
    }

    private function propertyModel(): ?Property
    {
        if (! $this->propertyId) {
            return null;
        }

        $property = Property::query()->find($this->propertyId);

        abort_unless(! $property || $property->isOwnedBy(auth()->user()), 403);

        return $property;
    }

    private function draftStatus(): string
    {
        $property = $this->propertyModel();

        return $property?->status === PropertyStatus::Active
            ? PropertyStatus::Active->value
            : PropertyStatus::Draft->value;
    }

    private function translatedLookupLabel($translations, string $fallback): string
    {
        $locale = app()->getLocale();
        $translation = $translations->firstWhere('locale', $locale)
            ?: $translations->firstWhere('locale', config('app.fallback_locale'));

        return $translation?->name ?: __('host.lookup_fallback.item');
    }

    /**
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    private function integerIds(array $ids): array
    {
        return collect($ids)
            ->map(fn (int|string $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
