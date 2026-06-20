<?php

namespace App\Livewire\Host\Properties;

use App\Livewire\Host\Properties\Concerns\HandlesPropertyStep;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PropertyMainInfoStep extends Component
{
    use HandlesPropertyStep;

    public string $titleEn = '';

    public string $titleRu = '';

    public string $shortDescriptionEn = '';

    public string $shortDescriptionRu = '';

    public string $fullDescriptionEn = '';

    public string $fullDescriptionRu = '';

    public string $propertyType = '';

    public string $propertySubtype = '';

    public string $district = '';

    public string $street = '';

    public string $houseNumber = '';

    public string $apartmentNumber = '';

    public ?int $floor = null;

    public ?int $totalFloors = null;

    public bool $hasElevator = false;

    public bool $showExactAddressBeforeBooking = false;

    public bool $showExactAddressAfterConfirmation = true;

    public bool $showExactAddressAfterPayment = true;

    public bool $showOnlyApproximateLocation = true;

    public function mount(Property $property): void
    {
        $this->mountProperty($property);

        $property->loadMissing('translations');

        $translationEn = $property->translations->firstWhere('locale', 'en');
        $translationRu = $property->translations->firstWhere('locale', 'ru');

        $this->titleEn = (string) ($translationEn?->title ?? $property->title ?? '');
        $this->titleRu = (string) ($translationRu?->title ?? '');
        $this->shortDescriptionEn = (string) ($translationEn?->short_description ?? $translationEn?->summary ?? '');
        $this->shortDescriptionRu = (string) ($translationRu?->short_description ?? $translationRu?->summary ?? '');
        $this->fullDescriptionEn = (string) ($translationEn?->full_description ?? $translationEn?->description ?? '');
        $this->fullDescriptionRu = (string) ($translationRu?->full_description ?? $translationRu?->description ?? '');
        $this->propertyType = (string) ($property->property_type?->value ?? $property->property_type ?? $property->type?->value ?? $property->type ?? '');
        $this->propertySubtype = (string) ($property->property_subtype ?? '');
        $this->district = (string) ($property->district ?? '');
        $this->street = (string) ($property->street ?? $property->address_line_1 ?? '');
        $this->houseNumber = (string) ($property->house_number ?? $property->building ?? '');
        $this->apartmentNumber = (string) ($property->apartment_number ?? $property->apartment ?? '');
        $this->floor = $property->floor;
        $this->totalFloors = $property->total_floors;
        $this->hasElevator = (bool) $property->has_elevator;
        $this->showExactAddressBeforeBooking = (bool) $property->show_exact_address_before_booking;
        $this->showExactAddressAfterConfirmation = (bool) $property->show_exact_address_after_confirmation;
        $this->showExactAddressAfterPayment = (bool) $property->show_exact_address_after_payment;
        $this->showOnlyApproximateLocation = (bool) $property->show_only_approximate_location;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'titleEn' => ['required', 'string', 'max:160'],
            'titleRu' => ['nullable', 'string', 'max:160'],
            'shortDescriptionEn' => ['nullable', 'string', 'max:500'],
            'shortDescriptionRu' => ['nullable', 'string', 'max:500'],
            'fullDescriptionEn' => ['nullable', 'string', 'max:5000'],
            'fullDescriptionRu' => ['nullable', 'string', 'max:5000'],
            'propertyType' => ['required', 'string', 'max:80'],
            'propertySubtype' => ['nullable', 'string', 'max:80'],
            'district' => ['nullable', 'string', 'max:120'],
            'street' => ['nullable', 'string', 'max:160'],
            'houseNumber' => ['nullable', 'string', 'max:32'],
            'apartmentNumber' => ['nullable', 'string', 'max:32'],
            'floor' => ['nullable', 'integer', 'min:0', 'max:300'],
            'totalFloors' => ['nullable', 'integer', 'min:0', 'max:300'],
            'hasElevator' => ['boolean'],
            'showExactAddressBeforeBooking' => ['boolean'],
            'showExactAddressAfterConfirmation' => ['boolean'],
            'showExactAddressAfterPayment' => ['boolean'],
            'showOnlyApproximateLocation' => ['boolean'],
        ]);

        $property = $this->property();
        $property->update([
            'title' => $validated['titleEn'],
            'type' => $validated['propertyType'],
            'property_type' => $validated['propertyType'],
            'property_subtype' => $validated['propertySubtype'] ?: null,
            'district' => $validated['district'] ?: null,
            'street' => $validated['street'] ?: null,
            'address_line_1' => $validated['street'] ?: null,
            'house_number' => $validated['houseNumber'] ?: null,
            'building' => $validated['houseNumber'] ?: null,
            'apartment_number' => $validated['apartmentNumber'] ?: null,
            'apartment' => $validated['apartmentNumber'] ?: null,
            'floor' => $validated['floor'],
            'total_floors' => $validated['totalFloors'],
            'has_elevator' => $validated['hasElevator'],
            'show_exact_address_before_booking' => $validated['showExactAddressBeforeBooking'],
            'show_exact_address_after_confirmation' => $validated['showExactAddressAfterConfirmation'],
            'show_exact_address_after_payment' => $validated['showExactAddressAfterPayment'],
            'show_only_approximate_location' => $validated['showOnlyApproximateLocation'],
        ]);

        foreach (['en', 'ru'] as $locale) {
            $title = $validated['title'.ucfirst($locale)];
            $shortDescription = $validated['shortDescription'.ucfirst($locale)];
            $fullDescription = $validated['fullDescription'.ucfirst($locale)];

            if ($title === '' && $shortDescription === '' && $fullDescription === '') {
                continue;
            }

            $property->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $title ?: null,
                    'short_description' => $shortDescription ?: null,
                    'summary' => $shortDescription ?: null,
                    'full_description' => $fullDescription ?: null,
                    'description' => $fullDescription ?: null,
                ],
            );
        }

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.properties.property-main-info-step');
    }
}
