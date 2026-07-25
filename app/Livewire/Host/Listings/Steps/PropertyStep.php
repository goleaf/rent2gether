<?php

namespace App\Livewire\Host\Listings\Steps;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use App\Services\Catalog\AmenityRuleCatalog;
use App\Services\Catalog\AmenityRuleLookupService;
use App\Services\HostListings\Wizard\HostPropertyDraftService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PropertyStep extends Component
{
    #[Locked]
    public int $propertyId;

    public string $title = '';

    public string $type = PropertyType::Apartment->value;

    public string $address = '';

    public string $city = '';

    public string $description = '';

    public string $district = '';

    public int $roomsCount = 1;

    public int $bathroomsCount = 1;

    /** @var list<string> */
    public array $amenities = [];

    /** @var list<string> */
    public array $rules = [];

    public string $status = PropertyStatus::Draft->value;

    public function mount(int $propertyId): void
    {
        $property = $this->ownedProperty($propertyId);

        $this->propertyId = $property->id;
        $this->title = (string) $property->title;
        $this->type = $property->type?->value ?? PropertyType::Apartment->value;
        $this->address = (string) ($property->address_line_1 ?: $property->street);
        $this->city = (string) $property->city;
        $this->description = (string) $property->description;
        $this->district = (string) $property->district;
        $this->roomsCount = max(1, (int) ($property->rooms_count ?: 1));
        $this->bathroomsCount = max(0, (int) ($property->bathrooms_count ?: 1));
        $this->amenities = $this->storedList($property->getAttribute('amenities'));
        $this->rules = $this->storedList($property->getAttribute('rules'));
        $this->status = $property->status?->value ?? PropertyStatus::Draft->value;
    }

    public function save(HostPropertyDraftService $drafts): void
    {
        $host = auth()->user();

        if ($host instanceof User) {
            $validated = $this->validate($this->rules(), attributes: $this->validationAttributes());

            $drafts->createOrUpdateProperty($host, [
                'property_id' => $this->propertyId,
                'title' => str($validated['title'])->squish()->toString(),
                'type' => $validated['type'],
                'property_type' => $validated['type'],
                'address' => str($validated['address'] ?? '')->squish()->toString(),
                'city' => str($validated['city'])->squish()->toString(),
                'description' => str($validated['description'])->squish()->toString(),
                'district' => str($validated['district'] ?? '')->squish()->toString(),
                'rooms_count' => (int) $validated['roomsCount'],
                'bathrooms_count' => (int) $validated['bathroomsCount'],
                'amenities' => array_values($validated['amenities'] ?? []),
                'rules' => array_values($validated['rules'] ?? []),
                'status' => $validated['status'],
            ]);
            $this->dispatch('listing-step-saved');
        }
    }

    public function render(): View
    {
        return view('livewire.host.listings.steps.property-step', [
            'propertyTypeOptions' => $this->propertyTypeOptions(),
            'statusOptions' => $this->statusOptions(),
            'amenityOptions' => $this->amenityOptions(),
            'ruleOptions' => $this->ruleOptions(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'type' => ['required', ValidationRule::in($this->propertyTypeValues())],
            'address' => ['nullable', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'roomsCount' => ['required', 'integer', 'min:1', 'max:50'],
            'bathroomsCount' => ['required', 'integer', 'min:0', 'max:20'],
            'amenities' => ['array', 'max:12'],
            'amenities.*' => ['string', ValidationRule::in($this->amenityValues())],
            'rules' => ['array', 'max:12'],
            'rules.*' => ['string', ValidationRule::in($this->ruleValues())],
            'status' => ['required', ValidationRule::in($this->propertyStatusValues())],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'title' => __('listing_wizard.property.name'),
            'type' => __('listing_wizard.property.type'),
            'address' => __('listing_wizard.property.address'),
            'city' => __('listing_wizard.property.city'),
            'district' => __('listing_wizard.property.district'),
            'description' => __('listing_wizard.property.description'),
            'roomsCount' => __('listing_wizard.property.rooms_count'),
            'bathroomsCount' => __('listing_wizard.property.bathrooms_count'),
            'amenities' => __('listing_wizard.property.amenities'),
            'rules' => __('listing_wizard.property.rules'),
            'status' => __('listing_wizard.property.status'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function propertyTypeOptions(): array
    {
        return collect(PropertyType::cases())
            ->mapWithKeys(fn (PropertyType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(PropertyStatus::cases())
            ->mapWithKeys(fn (PropertyStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function amenityOptions(): array
    {
        return $this->catalogOptions(
            app(AmenityRuleLookupService::class)->amenityOptions(
                locale: app()->getLocale(),
                categories: ['property', 'kitchen', 'bathroom', 'safety', 'long_stay', 'accessibility', 'transport', 'storage', 'work_study'],
                limit: 24,
            ),
            AmenityRuleCatalog::amenities(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function ruleOptions(): array
    {
        return $this->catalogOptions(
            app(AmenityRuleLookupService::class)->ruleOptions(locale: app()->getLocale(), limit: 24),
            AmenityRuleCatalog::rules(),
        );
    }

    /**
     * @param  list<array{slug:string,label:string}>  $lookupOptions
     * @param  list<array{slug:string,en:string,ru:string}>  $fallbackOptions
     * @return array<string, string>
     */
    private function catalogOptions(array $lookupOptions, array $fallbackOptions): array
    {
        if ($lookupOptions !== []) {
            return collect($lookupOptions)
                ->mapWithKeys(fn (array $option): array => [$option['slug'] => $option['label']])
                ->all();
        }

        $locale = app()->getLocale();

        return collect($fallbackOptions)
            ->take(24)
            ->mapWithKeys(fn (array $option): array => [
                $option['slug'] => $option[$locale] ?? $option['en'],
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    private function propertyTypeValues(): array
    {
        return array_map(fn (PropertyType $type): string => $type->value, PropertyType::cases());
    }

    /**
     * @return list<string>
     */
    private function propertyStatusValues(): array
    {
        return array_map(fn (PropertyStatus $status): string => $status->value, PropertyStatus::cases());
    }

    /**
     * @return list<string>
     */
    private function amenityValues(): array
    {
        return collect(AmenityRuleCatalog::amenities())->pluck('slug')->all();
    }

    /**
     * @return list<string>
     */
    private function ruleValues(): array
    {
        return collect(AmenityRuleCatalog::rules())->pluck('slug')->all();
    }

    private function ownedProperty(int $propertyId): Property
    {
        $property = Property::query()
            ->select([
                'id',
                'host_user_id',
                'user_id',
                'title',
                'type',
                'property_type',
                'address_line_1',
                'street',
                'city',
                'district',
                'description',
                'rooms_count',
                'bathrooms_count',
                'amenities',
                'rules',
                'status',
            ])
            ->findOrFail($propertyId);

        $host = auth()->user();
        abort_unless($host instanceof User && $property->isOwnedBy($host), 403);

        return $property;
    }

    /**
     * @return list<string>
     */
    private function storedList(mixed $value): array
    {
        return collect(is_array($value) ? $value : [])
            ->filter(fn (mixed $item): bool => is_string($item) && $item !== '')
            ->values()
            ->all();
    }
}
