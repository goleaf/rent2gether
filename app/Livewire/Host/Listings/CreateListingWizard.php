<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use App\Models\User;
use App\Services\HostListings\Wizard\HostListingPublishService;
use App\Services\HostListings\Wizard\HostListingWizardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreateListingWizard extends Component
{
    #[Locked]
    public ?int $propertyId = null;

    public string $step = 'property';

    public bool $wasSaved = false;

    public function mount(?int $propertyId = null): void
    {
        $wizard = app(HostListingWizardService::class);
        $host = auth()->user();

        abort_unless($host instanceof User, 403);

        if ($propertyId) {
            $property = Property::query()->findOrFail($propertyId);
            abort_unless($property->isOwnedBy($host), 403);
            $session = $wizard->resume($host, $property);
        } else {
            $session = $wizard->start($host);
        }

        $this->propertyId = $session->property_id;
        $this->step = $session->current_step;
    }

    public function saveDraft(HostListingWizardService $wizard): void
    {
        $property = $this->property();
        $host = auth()->user();

        if ($property instanceof Property && $host instanceof User) {
            $wizard->saveStep($host, $property, $this->step, ['next_step' => $this->step]);
            $this->wasSaved = true;
        }
    }

    public function next(HostListingWizardService $wizard): void
    {
        $property = $this->property();
        $host = auth()->user();
        $next = $this->nextStep();

        if ($property instanceof Property && $host instanceof User) {
            $wizard->markStepCompleted($property, $this->step);
            $wizard->saveStep($host, $property, $this->step, ['next_step' => $next]);
            $this->step = $next;
            $this->wasSaved = true;
        }
    }

    public function back(): void
    {
        $steps = $this->steps();
        $index = max(0, array_search($this->step, $steps, true) - 1);
        $this->step = $steps[$index];
        $this->wasSaved = false;
    }

    public function publish(HostListingPublishService $publisher): void
    {
        $property = $this->property();
        $host = auth()->user();

        if ($property instanceof Property && $host instanceof User) {
            $publisher->publishIfReady($host, $property);
            $this->dispatch('listing-published');
        }
    }

    public function render(): View
    {
        return view('livewire.host.listings.create-listing-wizard', [
            'property' => $this->property(),
            'steps' => $this->steps(),
            'currentIndex' => array_search($this->step, $this->steps(), true) + 1,
        ])->layout('layouts.app', [
            'title' => __('listing_wizard.title'),
        ]);
    }

    private function property(): ?Property
    {
        return $this->propertyId ? Property::query()->find($this->propertyId) : null;
    }

    /**
     * @return list<string>
     */
    private function steps(): array
    {
        return ['property', 'rooms', 'sleeping_places', 'calendar', 'publish'];
    }

    private function nextStep(): string
    {
        $steps = $this->steps();
        $index = array_search($this->step, $steps, true);

        return $steps[min(count($steps) - 1, $index + 1)];
    }
}
