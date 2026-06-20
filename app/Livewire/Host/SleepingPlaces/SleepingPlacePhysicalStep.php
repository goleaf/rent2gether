<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use App\Services\SleepingPlaces\SleepingPlacePhysicalService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlacePhysicalStep extends Component
{
    use HandlesSleepingPlaceStep;

    public ?int $lengthCm = null;

    public ?int $widthCm = null;

    public ?int $heightCm = null;

    public ?int $heightFromFloorCm = null;

    public ?int $clearanceAboveCm = null;

    public bool $ladderAvailable = false;

    public string $ladderComfortLevel = '';

    public bool $safetyRailAvailable = false;

    public ?int $safetyRailHeightCm = null;

    public ?int $maxWeightKg = null;

    public bool $suitableForTallPerson = false;

    public bool $suitableForHeavyPerson = false;

    public bool $suitableForElderly = false;

    public bool $suitableForLimitedMobility = false;

    public bool $notSuitableForLimitedMobility = false;

    public string $frameMaterial = '';

    public string $frameStabilityLevel = '';

    public string $squeakLevel = '';

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);
        $sleepingPlace->loadMissing('physicalDetails');
        $details = $sleepingPlace->physicalDetails;

        $this->lengthCm = $details?->length_cm ?? $sleepingPlace->length_cm;
        $this->widthCm = $details?->width_cm ?? $sleepingPlace->width_cm;
        $this->heightCm = $details?->height_cm ?? $sleepingPlace->height_cm;
        $this->heightFromFloorCm = $details?->height_from_floor_cm;
        $this->clearanceAboveCm = $details?->clearance_above_cm;
        $this->ladderAvailable = (bool) $details?->ladder_available;
        $this->ladderComfortLevel = (string) ($details?->ladder_comfort_level ?? '');
        $this->safetyRailAvailable = (bool) $details?->safety_rail_available;
        $this->safetyRailHeightCm = $details?->safety_rail_height_cm;
        $this->maxWeightKg = $details?->max_weight_kg;
        $this->suitableForTallPerson = (bool) ($details?->suitable_for_tall_person ?? $sleepingPlace->suitable_for_tall_person);
        $this->suitableForHeavyPerson = (bool) $details?->suitable_for_heavy_person;
        $this->suitableForElderly = (bool) ($details?->suitable_for_elderly ?? $sleepingPlace->suitable_for_elderly);
        $this->suitableForLimitedMobility = (bool) ($details?->suitable_for_limited_mobility ?? $sleepingPlace->suitable_for_limited_mobility);
        $this->notSuitableForLimitedMobility = (bool) $details?->not_suitable_for_limited_mobility;
        $this->frameMaterial = (string) ($details?->frame_material ?? '');
        $this->frameStabilityLevel = (string) ($details?->frame_stability_level ?? '');
        $this->squeakLevel = (string) ($details?->squeak_level ?? '');
    }

    public function save(SleepingPlacePhysicalService $service): void
    {
        $validated = $this->validate([
            'lengthCm' => ['nullable', 'integer', 'min:1', 'max:500'],
            'widthCm' => ['nullable', 'integer', 'min:1', 'max:500'],
            'heightCm' => ['nullable', 'integer', 'min:1', 'max:500'],
            'heightFromFloorCm' => ['nullable', 'integer', 'min:0', 'max:500'],
            'clearanceAboveCm' => ['nullable', 'integer', 'min:0', 'max:500'],
            'ladderAvailable' => ['boolean'],
            'ladderComfortLevel' => ['nullable', 'string', 'max:40'],
            'safetyRailAvailable' => ['boolean'],
            'safetyRailHeightCm' => ['nullable', 'integer', 'min:0', 'max:200'],
            'maxWeightKg' => ['nullable', 'integer', 'min:1', 'max:500'],
            'suitableForTallPerson' => ['boolean'],
            'suitableForHeavyPerson' => ['boolean'],
            'suitableForElderly' => ['boolean'],
            'suitableForLimitedMobility' => ['boolean'],
            'notSuitableForLimitedMobility' => ['boolean'],
            'frameMaterial' => ['nullable', 'string', 'max:80'],
            'frameStabilityLevel' => ['nullable', 'string', 'max:40'],
            'squeakLevel' => ['nullable', 'string', 'max:40'],
        ], attributes: __('sleeping_place.validation_attributes'));

        $service->updatePhysicalDetails($this->sleepingPlace(), [
            'length_cm' => $validated['lengthCm'],
            'width_cm' => $validated['widthCm'],
            'height_cm' => $validated['heightCm'],
            'height_from_floor_cm' => $validated['heightFromFloorCm'],
            'clearance_above_cm' => $validated['clearanceAboveCm'],
            'ladder_available' => $validated['ladderAvailable'],
            'ladder_comfort_level' => $validated['ladderComfortLevel'] ?: null,
            'safety_rail_available' => $validated['safetyRailAvailable'],
            'safety_rail_height_cm' => $validated['safetyRailHeightCm'],
            'max_weight_kg' => $validated['maxWeightKg'],
            'suitable_for_tall_person' => $validated['suitableForTallPerson'],
            'suitable_for_heavy_person' => $validated['suitableForHeavyPerson'],
            'suitable_for_elderly' => $validated['suitableForElderly'],
            'suitable_for_limited_mobility' => $validated['suitableForLimitedMobility'],
            'not_suitable_for_limited_mobility' => $validated['notSuitableForLimitedMobility'],
            'frame_material' => $validated['frameMaterial'] ?: null,
            'frame_stability_level' => $validated['frameStabilityLevel'] ?: null,
            'squeak_level' => $validated['squeakLevel'] ?: null,
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-physical-step');
    }
}
