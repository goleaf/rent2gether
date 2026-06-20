<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\Listings\BaseListingStepComponent;

class SleepingPlaceBatchCreateSheet extends BaseListingStepComponent
{
    protected function titleKey(): string
    {
        return 'sleeping_place_details.batch.title';
    }

    protected function helperKey(): string
    {
        return 'sleeping_place_details.batch.helper';
    }

    protected function actionKey(): string
    {
        return 'listing_wizard.actions.create_multiple_places';
    }
}
