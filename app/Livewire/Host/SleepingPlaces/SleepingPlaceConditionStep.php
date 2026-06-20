<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use App\Services\SleepingPlaces\SleepingPlaceConditionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlaceConditionStep extends Component
{
    use HandlesSleepingPlaceStep;

    public string $conditionState = '';

    public string $frameCondition = '';

    public string $mattressCondition = '';

    public string $beddingCondition = '';

    public string $curtainCondition = '';

    public string $lampCondition = '';

    public string $socketCondition = '';

    public string $lockerCondition = '';

    public bool $hasDamage = false;

    public bool $hasStains = false;

    public bool $hasSmell = false;

    public bool $squeaks = false;

    public bool $needsRepair = false;

    public bool $needsMattressReplacement = false;

    public string $lastCleanedAt = '';

    public string $lastBeddingChangedAt = '';

    public string $lastCheckedAt = '';

    public string $hostConditionNote = '';

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);
        $sleepingPlace->loadMissing('conditionDetails');
        $details = $sleepingPlace->conditionDetails;

        $this->conditionState = (string) ($details?->condition_state ?? '');
        $this->frameCondition = (string) ($details?->frame_condition ?? '');
        $this->mattressCondition = (string) ($details?->mattress_condition ?? '');
        $this->beddingCondition = (string) ($details?->bedding_condition ?? '');
        $this->curtainCondition = (string) ($details?->curtain_condition ?? '');
        $this->lampCondition = (string) ($details?->lamp_condition ?? '');
        $this->socketCondition = (string) ($details?->socket_condition ?? '');
        $this->lockerCondition = (string) ($details?->locker_condition ?? '');
        $this->hasDamage = (bool) $details?->has_damage;
        $this->hasStains = (bool) $details?->has_stains;
        $this->hasSmell = (bool) $details?->has_smell;
        $this->squeaks = (bool) $details?->squeaks;
        $this->needsRepair = (bool) $details?->needs_repair;
        $this->needsMattressReplacement = (bool) $details?->needs_mattress_replacement;
        $this->lastCleanedAt = (string) ($details?->last_cleaned_at?->toDateString() ?? '');
        $this->lastBeddingChangedAt = (string) ($details?->last_bedding_changed_at?->toDateString() ?? '');
        $this->lastCheckedAt = (string) ($details?->last_checked_at?->toDateString() ?? '');
        $this->hostConditionNote = (string) ($details?->host_condition_note ?? '');
    }

    public function save(SleepingPlaceConditionService $service): void
    {
        $validated = $this->validate([
            'conditionState' => ['nullable', 'string', 'max:40'],
            'frameCondition' => ['nullable', 'string', 'max:40'],
            'mattressCondition' => ['nullable', 'string', 'max:40'],
            'beddingCondition' => ['nullable', 'string', 'max:40'],
            'curtainCondition' => ['nullable', 'string', 'max:40'],
            'lampCondition' => ['nullable', 'string', 'max:40'],
            'socketCondition' => ['nullable', 'string', 'max:40'],
            'lockerCondition' => ['nullable', 'string', 'max:40'],
            'hasDamage' => ['boolean'],
            'hasStains' => ['boolean'],
            'hasSmell' => ['boolean'],
            'squeaks' => ['boolean'],
            'needsRepair' => ['boolean'],
            'needsMattressReplacement' => ['boolean'],
            'lastCleanedAt' => ['nullable', 'date'],
            'lastBeddingChangedAt' => ['nullable', 'date'],
            'lastCheckedAt' => ['nullable', 'date'],
            'hostConditionNote' => ['nullable', 'string', 'max:1000'],
        ], attributes: __('sleeping_place.validation_attributes'));

        $service->updateConditionDetails($this->sleepingPlace(), [
            'condition_state' => $validated['conditionState'] ?: null,
            'frame_condition' => $validated['frameCondition'] ?: null,
            'mattress_condition' => $validated['mattressCondition'] ?: null,
            'bedding_condition' => $validated['beddingCondition'] ?: null,
            'curtain_condition' => $validated['curtainCondition'] ?: null,
            'lamp_condition' => $validated['lampCondition'] ?: null,
            'socket_condition' => $validated['socketCondition'] ?: null,
            'locker_condition' => $validated['lockerCondition'] ?: null,
            'has_damage' => $validated['hasDamage'],
            'has_stains' => $validated['hasStains'],
            'has_smell' => $validated['hasSmell'],
            'squeaks' => $validated['squeaks'],
            'needs_repair' => $validated['needsRepair'],
            'needs_mattress_replacement' => $validated['needsMattressReplacement'],
            'last_cleaned_at' => $validated['lastCleanedAt'] ?: null,
            'last_bedding_changed_at' => $validated['lastBeddingChangedAt'] ?: null,
            'last_checked_at' => $validated['lastCheckedAt'] ?: null,
            'host_condition_note' => $validated['hostConditionNote'] ?: null,
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-condition-step');
    }
}
