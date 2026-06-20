<?php

namespace App\Livewire\Host\Properties\Concerns;

use App\Models\Property;
use App\Models\User;

trait HandlesPropertyStep
{
    public int $propertyId;

    public bool $saved = false;

    public bool $wasSaved = false;

    protected function mountProperty(Property $property): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $property->isOwnedBy($user), 403);

        $this->propertyId = $property->id;
    }

    protected function property(): Property
    {
        return Property::query()->findOrFail($this->propertyId);
    }

    protected function markSaved(): void
    {
        $this->saved = true;
        $this->wasSaved = true;
    }
}
