<?php

namespace App\Livewire\Host\Properties;

use App\Livewire\Host\Listings\BaseListingStepComponent;

class PropertyAmenitiesStep extends BaseListingStepComponent
{
    protected function titleKey(): string
    {
        return 'property_amenities.title';
    }

    protected function helperKey(): string
    {
        return 'property_amenities.helper';
    }
}
