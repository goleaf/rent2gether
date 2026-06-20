<?php

namespace App\Livewire\Host\Listings;

use App\Services\HostListings\Wizard\HostListingReadinessService;
use Illuminate\Contracts\View\View;

class BeforePublishChecklist extends ListingReadinessChecklist
{
    public function render(HostListingReadinessService $readiness): View
    {
        $view = parent::render($readiness);

        return view('livewire.host.listings.before-publish-checklist', $view->getData());
    }
}
