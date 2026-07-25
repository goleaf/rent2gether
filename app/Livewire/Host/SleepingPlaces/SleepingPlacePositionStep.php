<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use App\Services\SleepingPlaces\SleepingPlacePositionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlacePositionStep extends Component
{
    use HandlesSleepingPlaceStep;

    public string $privacyLevel = '';

    public bool $hasCurtain = false;

    public bool $hasPersonalLamp = false;

    public bool $hasPowerSocket = false;

    public ?int $powerSocketsCount = null;

    public bool $hasUsbCharger = false;

    public bool $hasShelf = false;

    public bool $hasHook = false;

    public bool $nearDoor = false;

    public bool $nearWindow = false;

    public bool $nearRadiator = false;

    public bool $nearAirConditioner = false;

    public bool $nearPowerSocket = false;

    public bool $nearPassage = false;

    public string $noiseLevelNearPlace = '';

    public string $lightLevelNearPlace = '';

    public bool $morningLight = false;

    public bool $draftNearby = false;

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);
        $sleepingPlace->loadMissing('positionDetails');
        $details = $sleepingPlace->positionDetails;

        $this->privacyLevel = (string) ($details?->privacy_level ?? $sleepingPlace->privacy_level ?? '');
        $this->hasCurtain = (bool) ($details?->has_curtain ?? $sleepingPlace->has_curtain);
        $this->hasPersonalLamp = (bool) ($details?->has_personal_lamp ?? $sleepingPlace->has_lamp);
        $this->hasPowerSocket = (bool) ($details?->has_power_socket ?? $sleepingPlace->has_power_socket);
        $this->powerSocketsCount = $details?->power_sockets_count;
        $this->hasUsbCharger = (bool) ($details?->has_usb_charger ?? $sleepingPlace->has_usb);
        $this->hasShelf = (bool) ($details?->has_shelf ?? $sleepingPlace->has_shelf);
        $this->hasHook = (bool) ($details?->has_hook ?? $sleepingPlace->has_hook);
        $this->nearDoor = (bool) ($details?->near_door ?? $sleepingPlace->near_door);
        $this->nearWindow = (bool) ($details?->near_window ?? $sleepingPlace->near_window);
        $this->nearRadiator = (bool) ($details?->near_radiator ?? $sleepingPlace->near_radiator);
        $this->nearAirConditioner = (bool) ($details?->near_air_conditioner ?? $sleepingPlace->near_air_conditioner);
        $this->nearPowerSocket = (bool) ($details?->near_power_socket ?? $details?->near_socket);
        $this->nearPassage = (bool) $details?->near_passage;
        $this->noiseLevelNearPlace = (string) ($details?->noise_level_near_place ?? $sleepingPlace->noise_level ?? '');
        $this->lightLevelNearPlace = (string) ($details?->light_level_near_place ?? '');
        $this->morningLight = (bool) $details?->morning_light;
        $this->draftNearby = (bool) $details?->draft_nearby;
    }

    public function save(SleepingPlacePositionService $service): void
    {
        $validated = $this->validate([
            'privacyLevel' => ['nullable', 'string', 'max:40'],
            'hasCurtain' => ['boolean'],
            'hasPersonalLamp' => ['boolean'],
            'hasPowerSocket' => ['boolean'],
            'powerSocketsCount' => ['nullable', 'integer', 'min:0', 'max:10'],
            'hasUsbCharger' => ['boolean'],
            'hasShelf' => ['boolean'],
            'hasHook' => ['boolean'],
            'nearDoor' => ['boolean'],
            'nearWindow' => ['boolean'],
            'nearRadiator' => ['boolean'],
            'nearAirConditioner' => ['boolean'],
            'nearPowerSocket' => ['boolean'],
            'nearPassage' => ['boolean'],
            'noiseLevelNearPlace' => ['nullable', 'string', 'max:40'],
            'lightLevelNearPlace' => ['nullable', 'string', 'max:40'],
            'morningLight' => ['boolean'],
            'draftNearby' => ['boolean'],
        ], attributes: __('sleeping_place.validation_attributes'));

        $service->updatePositionDetails($this->sleepingPlace(), [
            'privacy_level' => $validated['privacyLevel'] ?: null,
            'has_curtain' => $validated['hasCurtain'],
            'has_personal_lamp' => $validated['hasPersonalLamp'],
            'has_power_socket' => $validated['hasPowerSocket'],
            'power_sockets_count' => $validated['powerSocketsCount'],
            'has_usb_charger' => $validated['hasUsbCharger'],
            'has_shelf' => $validated['hasShelf'],
            'has_hook' => $validated['hasHook'],
            'near_door' => $validated['nearDoor'],
            'near_window' => $validated['nearWindow'],
            'near_radiator' => $validated['nearRadiator'],
            'near_air_conditioner' => $validated['nearAirConditioner'],
            'near_power_socket' => $validated['nearPowerSocket'],
            'near_socket' => $validated['nearPowerSocket'],
            'near_passage' => $validated['nearPassage'],
            'noise_level_near_place' => $validated['noiseLevelNearPlace'] ?: null,
            'light_level_near_place' => $validated['lightLevelNearPlace'] ?: null,
            'morning_light' => $validated['morningLight'],
            'draft_nearby' => $validated['draftNearby'],
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-position-step');
    }
}
