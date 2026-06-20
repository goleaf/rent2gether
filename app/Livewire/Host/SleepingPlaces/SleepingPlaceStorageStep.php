<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use App\Services\SleepingPlaces\SleepingPlaceStorageService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlaceStorageStep extends Component
{
    use HandlesSleepingPlaceStep;

    public bool $hasShoeSpace = false;

    public bool $hasLuggageSpace = false;

    public bool $hasBackpackSpace = false;

    public bool $hasPersonalLocker = false;

    public bool $lockerHasLock = false;

    public bool $lockProvided = false;

    public bool $guestShouldBringLock = false;

    public bool $canStoreValuables = false;

    public bool $canStoreDocuments = false;

    public bool $canStoreLaptop = false;

    public string $lockerSize = '';

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);
        $sleepingPlace->loadMissing('storageDetails');
        $details = $sleepingPlace->storageDetails;

        $this->hasShoeSpace = (bool) $details?->has_shoe_space;
        $this->hasLuggageSpace = (bool) ($details?->has_luggage_space ?? $sleepingPlace->has_luggage_space);
        $this->hasBackpackSpace = (bool) $details?->has_backpack_space;
        $this->hasPersonalLocker = (bool) ($details?->has_personal_locker ?? $sleepingPlace->has_locker);
        $this->lockerHasLock = (bool) ($details?->locker_has_lock ?? $sleepingPlace->locker_has_lock);
        $this->lockProvided = (bool) $details?->lock_provided;
        $this->guestShouldBringLock = (bool) $details?->guest_should_bring_lock;
        $this->canStoreValuables = (bool) $details?->can_store_valuables;
        $this->canStoreDocuments = (bool) $details?->can_store_documents;
        $this->canStoreLaptop = (bool) $details?->can_store_laptop;
        $this->lockerSize = (string) ($details?->locker_size ?? '');
    }

    public function save(SleepingPlaceStorageService $service): void
    {
        $validated = $this->validate([
            'hasShoeSpace' => ['boolean'],
            'hasLuggageSpace' => ['boolean'],
            'hasBackpackSpace' => ['boolean'],
            'hasPersonalLocker' => ['boolean'],
            'lockerHasLock' => ['boolean'],
            'lockProvided' => ['boolean'],
            'guestShouldBringLock' => ['boolean'],
            'canStoreValuables' => ['boolean'],
            'canStoreDocuments' => ['boolean'],
            'canStoreLaptop' => ['boolean'],
            'lockerSize' => ['nullable', 'string', 'max:40'],
        ], attributes: __('sleeping_place.validation_attributes'));

        $service->updateStorageDetails($this->sleepingPlace(), [
            'has_shoe_space' => $validated['hasShoeSpace'],
            'has_luggage_space' => $validated['hasLuggageSpace'],
            'has_backpack_space' => $validated['hasBackpackSpace'],
            'has_personal_locker' => $validated['hasPersonalLocker'],
            'locker_has_lock' => $validated['lockerHasLock'],
            'lock_provided' => $validated['lockProvided'],
            'guest_should_bring_lock' => $validated['guestShouldBringLock'],
            'can_store_valuables' => $validated['canStoreValuables'],
            'can_store_documents' => $validated['canStoreDocuments'],
            'can_store_laptop' => $validated['canStoreLaptop'],
            'locker_size' => $validated['lockerSize'] ?: null,
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-storage-step');
    }
}
