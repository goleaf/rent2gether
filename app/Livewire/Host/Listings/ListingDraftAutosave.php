<?php

namespace App\Livewire\Host\Listings;

class ListingDraftAutosave extends BaseListingStepComponent
{
    protected function titleKey(): string
    {
        return 'listing_wizard.autosave.title';
    }

    protected function helperKey(): string
    {
        return 'listing_wizard.messages.autosaved';
    }

    protected function actionKey(): string
    {
        return 'listing_wizard.actions.save_draft';
    }
}
