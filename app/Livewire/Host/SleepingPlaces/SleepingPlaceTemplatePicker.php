<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\Listings\BaseListingStepComponent;

class SleepingPlaceTemplatePicker extends BaseListingStepComponent
{
    protected function titleKey(): string
    {
        return 'sleeping_place_details.templates.title';
    }

    protected function helperKey(): string
    {
        return 'sleeping_place_details.templates.helper';
    }
}
