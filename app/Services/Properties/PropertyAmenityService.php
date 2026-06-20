<?php

namespace App\Services\Properties;

use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PropertyAmenityService
{
    public function __construct(private readonly PropertyCreationService $properties) {}

    /**
     * @param  array<int, array<string, mixed>>  $amenities
     * @return Collection<int, PropertyAmenity>
     */
    public function save(User $host, Property $property, array $amenities): Collection
    {
        return $this->properties->saveAmenities($host, $property, $amenities);
    }
}
