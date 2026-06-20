<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class PropertyService
{
    public function __construct(
        private readonly DomainOwnershipService $ownership,
        private readonly UserRoleModeService $roles,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function create(User $host, array $data): Property
    {
        $this->ensureCanHost($host);

        return Property::query()->create($this->payload($host, $data));
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function update(User $host, Property $property, array $data): Property
    {
        $this->ownership->ensureHostOwnsProperty($host, $property);
        $property->update($this->payload($host, $data, false));

        return $property->refresh();
    }

    private function ensureCanHost(User $host): void
    {
        if (! $this->roles->canCreateHostObjects($host)) {
            throw new AuthorizationException(__('domain.errors.host_mode_required'));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(User $host, array $data, bool $creating = true): array
    {
        $street = $data['street_name'] ?? $data['street'] ?? null;
        $floorsCount = $data['floors_count'] ?? $data['total_floors'] ?? null;
        $maxResidents = $data['max_residents_count'] ?? $data['max_residents'] ?? null;

        return array_filter([
            'user_id' => $host->id,
            'host_user_id' => $host->id,
            'title' => $data['title'] ?? null,
            'type' => $data['type'] ?? $data['property_type'] ?? PropertyType::Apartment->value,
            'property_type' => $data['property_type'] ?? $data['type'] ?? PropertyType::Apartment->value,
            'description' => $data['description'] ?? null,
            'country' => $data['country'] ?? 'local',
            'city' => $data['city'] ?? 'local',
            'country_id' => $data['country_id'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'district' => $data['district'] ?? null,
            'street' => $street,
            'street_name' => $street,
            'house_number' => $data['house_number'] ?? null,
            'building' => $data['house_number'] ?? null,
            'apartment_number' => $data['apartment_number'] ?? null,
            'apartment' => $data['apartment_number'] ?? null,
            'floor' => $data['floor'] ?? null,
            'total_floors' => $floorsCount,
            'floors_count' => $floorsCount,
            'has_elevator' => (bool) ($data['has_elevator'] ?? false),
            'total_area' => $data['total_area'] ?? null,
            'rooms_count' => $data['rooms_count'] ?? null,
            'bedrooms_count' => $data['bedrooms_count'] ?? null,
            'bathrooms_count' => $data['bathrooms_count'] ?? null,
            'showers_count' => $data['showers_count'] ?? null,
            'kitchens_count' => $data['kitchens_count'] ?? null,
            'balconies_count' => $data['balconies_count'] ?? null,
            'max_residents' => $maxResidents,
            'max_residents_count' => $maxResidents,
            'current_residents_count' => $data['current_residents_count'] ?? 0,
            'free_places_count' => $data['free_places_count'] ?? 0,
            'occupied_places_count' => $data['occupied_places_count'] ?? 0,
            'status' => $data['status'] ?? PropertyStatus::Draft->value,
            'publication_status' => $data['publication_status'] ?? 'draft',
        ], fn (mixed $value): bool => $value !== null);
    }
}
