<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use App\Services\SleepingPlaces\SleepingPlaceComfortService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlaceComfortStep extends Component
{
    use HandlesSleepingPlaceStep;

    public string $mattressType = '';

    public string $mattressFirmness = '';

    public ?int $mattressThicknessCm = null;

    public string $mattressCondition = '';

    public string $mattressNewness = '';

    public bool $hasMattressProtector = false;

    public bool $hasPillow = true;

    public bool $hasBlanket = true;

    public bool $hasBedding = true;

    public bool $beddingIncluded = true;

    public bool $beddingChangedBeforeGuest = true;

    public bool $hasTowel = false;

    public bool $towelIncluded = false;

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);
        $sleepingPlace->loadMissing('comfortDetails');
        $details = $sleepingPlace->comfortDetails;

        $this->mattressType = (string) ($details?->mattress_type ?? $sleepingPlace->mattress_type ?? '');
        $this->mattressFirmness = (string) ($details?->mattress_firmness ?? $sleepingPlace->mattress_firmness ?? '');
        $this->mattressThicknessCm = $details?->mattress_thickness_cm;
        $this->mattressCondition = (string) ($details?->mattress_condition ?? '');
        $this->mattressNewness = (string) ($details?->mattress_newness ?? '');
        $this->hasMattressProtector = (bool) $details?->has_mattress_protector;
        $this->hasPillow = (bool) ($details?->has_pillow ?? $sleepingPlace->has_pillow);
        $this->hasBlanket = (bool) ($details?->has_blanket ?? $sleepingPlace->has_blanket);
        $this->hasBedding = (bool) ($details?->has_bedding ?? $sleepingPlace->has_bedding);
        $this->beddingIncluded = (bool) ($details?->bedding_included ?? $sleepingPlace->has_bedding);
        $this->beddingChangedBeforeGuest = (bool) ($details?->bedding_changed_before_guest ?? true);
        $this->hasTowel = (bool) ($details?->has_towel ?? $sleepingPlace->has_towel);
        $this->towelIncluded = (bool) ($details?->towel_included ?? $sleepingPlace->has_towel);
    }

    public function save(SleepingPlaceComfortService $service): void
    {
        $validated = $this->validate([
            'mattressType' => ['nullable', 'string', 'max:80'],
            'mattressFirmness' => ['nullable', 'string', 'max:40'],
            'mattressThicknessCm' => ['nullable', 'integer', 'min:1', 'max:80'],
            'mattressCondition' => ['nullable', 'string', 'max:40'],
            'mattressNewness' => ['nullable', 'string', 'max:40'],
            'hasMattressProtector' => ['boolean'],
            'hasPillow' => ['boolean'],
            'hasBlanket' => ['boolean'],
            'hasBedding' => ['boolean'],
            'beddingIncluded' => ['boolean'],
            'beddingChangedBeforeGuest' => ['boolean'],
            'hasTowel' => ['boolean'],
            'towelIncluded' => ['boolean'],
        ], attributes: __('sleeping_place.validation_attributes'));

        $service->updateComfortDetails($this->sleepingPlace(), [
            'mattress_type' => $validated['mattressType'] ?: null,
            'mattress_firmness' => $validated['mattressFirmness'] ?: null,
            'mattress_thickness_cm' => $validated['mattressThicknessCm'],
            'mattress_condition' => $validated['mattressCondition'] ?: null,
            'mattress_newness' => $validated['mattressNewness'] ?: null,
            'has_mattress_protector' => $validated['hasMattressProtector'],
            'has_pillow' => $validated['hasPillow'],
            'has_blanket' => $validated['hasBlanket'],
            'has_bedding' => $validated['hasBedding'],
            'bedding_included' => $validated['beddingIncluded'],
            'bedding_changed_before_guest' => $validated['beddingChangedBeforeGuest'],
            'has_towel' => $validated['hasTowel'],
            'towel_included' => $validated['towelIncluded'],
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-comfort-step');
    }
}
