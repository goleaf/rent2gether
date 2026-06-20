<?php

namespace App\Services\Properties;

use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\PropertyAddress;
use App\Models\PropertyAmenity;
use App\Models\PropertyRule;
use App\Models\User;
use App\Services\Domain\DomainOwnershipService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class PropertyCreationService
{
    public function __construct(
        private readonly PropertyService $properties,
        private readonly DomainOwnershipService $ownership,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function create(User $host, array $data): Property
    {
        return $this->properties->create($host, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function update(User $host, Property $property, array $data): Property
    {
        return $this->properties->update($host, $property, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function saveAddress(User $host, Property $property, array $data): PropertyAddress
    {
        $this->ownership->ensureHostOwnsProperty($host, $property);

        return PropertyAddress::query()->updateOrCreate(
            ['property_id' => $property->id],
            array_merge($this->only($data, [
                'country_id',
                'city_id',
                'district_id',
                'street_name',
                'house_number',
                'apartment_number',
                'postal_code',
                'floor',
                'latitude',
                'longitude',
                'approximate_latitude',
                'approximate_longitude',
                'public_location_label',
                'show_exact_address_after_booking',
                'show_street_before_booking',
                'show_district_before_booking',
            ]), ['property_id' => $property->id]),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $amenities
     * @return Collection<int, PropertyAmenity>
     *
     * @throws AuthorizationException
     */
    public function saveAmenities(User $host, Property $property, array $amenities): Collection
    {
        $this->ownership->ensureHostOwnsProperty($host, $property);
        $saved = new Collection;

        foreach ($amenities as $amenity) {
            $key = (string) ($amenity['amenity_key'] ?? '');

            if ($key === '') {
                continue;
            }

            $saved->push(PropertyAmenity::query()->updateOrCreate(
                ['property_id' => $property->id, 'amenity_key' => $key],
                [
                    'available' => (bool) ($amenity['available'] ?? true),
                    'description' => $amenity['description'] ?? null,
                    'visible_to_guest' => (bool) ($amenity['visible_to_guest'] ?? true),
                ],
            ));
        }

        return $saved;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rules
     * @return Collection<int, PropertyRule>
     *
     * @throws AuthorizationException
     */
    public function saveRules(User $host, Property $property, array $rules): Collection
    {
        $this->ownership->ensureHostOwnsProperty($host, $property);
        $saved = new Collection;

        foreach ($rules as $rule) {
            $key = (string) ($rule['rule_key'] ?? '');

            if ($key === '') {
                continue;
            }

            $saved->push(PropertyRule::query()->updateOrCreate(
                ['property_id' => $property->id, 'rule_key' => $key],
                [
                    'allowed' => (bool) ($rule['allowed'] ?? false),
                    'starts_at_time' => $rule['starts_at_time'] ?? null,
                    'ends_at_time' => $rule['ends_at_time'] ?? null,
                    'description' => $rule['description'] ?? null,
                    'strict' => (bool) ($rule['strict'] ?? false),
                    'visible_to_guest' => (bool) ($rule['visible_to_guest'] ?? true),
                    'status' => $rule['status'] ?? 'active',
                ],
            ));
        }

        return $saved;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function saveAccessDetails(User $host, Property $property, array $data): PropertyAccessDetail
    {
        $this->ownership->ensureHostOwnsProperty($host, $property);

        $payload = $this->only($data, [
            'entry_type',
            'has_intercom',
            'has_door_code',
            'has_key',
            'has_smart_lock',
            'has_key_safe',
            'self_check_in_available',
            'host_meeting_required',
            'representative_meeting_available',
            'entry_24_7',
            'night_entry_restrictions',
            'key_pickup_instruction',
            'key_return_instruction',
            'check_in_instruction',
            'night_entry_instruction',
            'door_code_encrypted',
            'intercom_code_encrypted',
            'key_safe_code_encrypted',
            'show_access_details_after_booking',
        ]);
        $payload['entrance_type'] = $payload['entry_type'] ?? $data['entrance_type'] ?? null;
        $payload['has_electronic_lock'] = (bool) ($payload['has_smart_lock'] ?? $data['has_electronic_lock'] ?? false);
        $payload['meet_host_required'] = (bool) ($payload['host_meeting_required'] ?? $data['meet_host_required'] ?? false);
        $payload['meet_host_representative_required'] = (bool) ($payload['representative_meeting_available'] ?? $data['meet_host_representative_required'] ?? false);
        $payload['access_24_7'] = (bool) ($payload['entry_24_7'] ?? $data['access_24_7'] ?? false);
        $payload['has_night_entry_restrictions'] = (bool) ($payload['night_entry_restrictions'] ?? $data['has_night_entry_restrictions'] ?? false);
        $payload['key_pickup_method'] = $data['key_pickup_method'] ?? ($payload['self_check_in_available'] ?? false ? 'self_check_in' : 'meet_host');
        $payload['check_in_instruction_available'] = filled($payload['check_in_instruction'] ?? null);

        return PropertyAccessDetail::query()->updateOrCreate(
            ['property_id' => $property->id],
            array_merge($payload, ['property_id' => $property->id]),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    private function only(array $data, array $keys): array
    {
        return collect($data)->only($keys)->all();
    }
}
