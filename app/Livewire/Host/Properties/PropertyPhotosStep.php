<?php

namespace App\Livewire\Host\Properties;

use App\Livewire\Host\Listings\BaseListingStepComponent;

class PropertyPhotosStep extends BaseListingStepComponent
{
    protected function titleKey(): string
    {
        return 'listing_wizard.property.photos';
    }

    protected function helperKey(): string
    {
        return 'listing_readiness.messages.property_photo';
    }
}
