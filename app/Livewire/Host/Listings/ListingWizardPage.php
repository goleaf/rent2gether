<?php

namespace App\Livewire\Host\Listings;

class ListingWizardPage extends BaseListingStepComponent
{
    protected function actionKey(): string
    {
        return 'listing_wizard.actions.save_draft';
    }
}
