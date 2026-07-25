<?php

namespace App\Services\HostListings\Wizard;

use App\Enums\PropertyRentalUnitType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;

class HostPropertyDraftService
{
    public function createOrUpdateProperty(User $host, array $data): Property
    {
        $property = isset($data['property_id'])
            ? Property::query()->whereKey($data['property_id'])->firstOrFail()
            : new Property;

        abort_unless(! $property->exists || $property->isOwnedBy($host), 403);

        $property->fill([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'rental_unit_type' => PropertyRentalUnitType::SleepingPlace->value,
            'title' => $data['title'] ?? $property->title ?? __('listing_wizard.defaults.property_title'),
            'type' => $data['type'] ?? $property->type?->value ?? PropertyType::Apartment->value,
            'property_type' => $data['property_type'] ?? $property->property_type?->value ?? PropertyType::Apartment->value,
            'description' => $data['description'] ?? $property->description ?? '',
            'country_id' => $data['country_id'] ?? $property->country_id,
            'region_id' => $data['region_id'] ?? $property->region_id,
            'city_id' => $data['city_id'] ?? $property->city_id,
            'country' => $data['country'] ?? $property->country ?? '',
            'city' => $data['city'] ?? $property->city ?? '',
            'district' => $data['district'] ?? $property->district ?? '',
            'street' => $data['street'] ?? $data['address'] ?? $property->street ?? '',
            'address_line_1' => $data['address_line_1'] ?? $data['address'] ?? $property->address_line_1,
            'house_number' => $data['house_number'] ?? $property->house_number,
            'apartment_number' => $data['apartment_number'] ?? $property->apartment_number,
            'floor' => $data['floor'] ?? $property->floor,
            'has_elevator' => $data['has_elevator'] ?? $property->has_elevator ?? false,
            'rooms_count' => $data['rooms_count'] ?? $property->rooms_count ?? 1,
            'bathrooms_count' => $data['bathrooms_count'] ?? $property->bathrooms_count ?? 1,
            'showers_count' => $data['showers_count'] ?? $property->showers_count ?? 1,
            'kitchens_count' => $data['kitchens_count'] ?? $property->kitchens_count ?? 1,
            'status' => $data['status'] ?? $property->status?->value ?? PropertyStatus::Draft->value,
            'publication_status' => $data['publication_status'] ?? $property->publication_status ?? 'draft',
        ]);

        $property->save();

        if (array_key_exists('rules', $data)) {
            $property->forceFill(['rules' => $data['rules']])->save();
        }

        if (array_key_exists('amenities', $data)) {
            $property->forceFill(['amenities' => $data['amenities']])->save();
        }

        return $property;
    }

    public function saveAmenities(Property $property, array $amenities): void
    {
        $property->amenities()->sync($amenities);
    }

    public function saveRules(Property $property, array $rules): void
    {
        $property->rules()->sync($rules);
    }

    public function savePhotos(Property $property, array $photos): void
    {
        // Uploads are handled by the existing media components; this method keeps the wizard API stable.
    }
}
