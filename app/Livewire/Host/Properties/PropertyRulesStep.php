<?php

namespace App\Livewire\Host\Properties;

use App\Livewire\Host\Listings\BaseListingStepComponent;

class PropertyRulesStep extends BaseListingStepComponent
{
    protected function titleKey(): string
    {
        return 'property_rules.title';
    }

    protected function helperKey(): string
    {
        return 'property_rules.helper';
    }
}
