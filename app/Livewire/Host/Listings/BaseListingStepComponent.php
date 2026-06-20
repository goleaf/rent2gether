<?php

namespace App\Livewire\Host\Listings;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

abstract class BaseListingStepComponent extends Component
{
    #[Locked]
    public ?int $propertyId = null;

    #[Locked]
    public ?int $roomId = null;

    #[Locked]
    public ?int $sleepingPlaceId = null;

    public function mount(?int $propertyId = null, ?int $roomId = null, ?int $sleepingPlaceId = null): void
    {
        $this->propertyId = $propertyId;
        $this->roomId = $roomId;
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function render(): View
    {
        return view('livewire.host.listings.simple-step-card', [
            'titleKey' => $this->titleKey(),
            'helperKey' => $this->helperKey(),
            'actionKey' => $this->actionKey(),
        ]);
    }

    protected function titleKey(): string
    {
        return 'listing_wizard.title';
    }

    protected function helperKey(): string
    {
        return 'listing_wizard.helper';
    }

    protected function actionKey(): string
    {
        return 'listing_wizard.actions.continue';
    }
}
