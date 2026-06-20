<?php

namespace App\Services\Properties;

use App\Models\Property;
use App\Models\PropertyRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PropertyRuleService
{
    public function __construct(private readonly PropertyCreationService $properties) {}

    /**
     * @param  array<int, array<string, mixed>>  $rules
     * @return Collection<int, PropertyRule>
     */
    public function save(User $host, Property $property, array $rules): Collection
    {
        return $this->properties->saveRules($host, $property, $rules);
    }
}
