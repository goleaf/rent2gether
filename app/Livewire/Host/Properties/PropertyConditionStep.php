<?php

namespace App\Livewire\Host\Properties;

use App\Livewire\Host\Properties\Concerns\HandlesPropertyStep;
use App\Models\Property;
use App\Services\Properties\PropertyConditionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PropertyConditionStep extends Component
{
    use HandlesPropertyStep;

    public string $repairState = '';

    public string $cleanlinessLevel = '';

    public string $smellLevel = '';

    public string $humidityLevel = '';

    public string $winterTemperatureLevel = '';

    public string $summerTemperatureLevel = '';

    public string $indoorNoiseLevel = '';

    public string $lightLevel = '';

    public bool $hasHeating = false;

    public bool $hasAirConditioning = false;

    public bool $hasHotWater = false;

    public bool $hasInsects = false;

    public bool $hasMold = false;

    public string $furnitureCondition = '';

    public string $plumbingCondition = '';

    public string $kitchenCondition = '';

    public string $floorCondition = '';

    public string $wallsCondition = '';

    public string $lastCleanedAt = '';

    public string $lastRepairedAt = '';

    public string $lastCheckedAt = '';

    public function mount(Property $property): void
    {
        $this->mountProperty($property);

        $property->loadMissing('conditionDetails');
        $details = $property->conditionDetails;

        $this->repairState = (string) ($details?->repair_state ?? '');
        $this->cleanlinessLevel = (string) ($details?->cleanliness_level ?? '');
        $this->smellLevel = (string) ($details?->smell_level ?? '');
        $this->humidityLevel = (string) ($details?->humidity_level ?? '');
        $this->winterTemperatureLevel = (string) ($details?->winter_temperature_level ?? '');
        $this->summerTemperatureLevel = (string) ($details?->summer_temperature_level ?? '');
        $this->indoorNoiseLevel = (string) ($details?->indoor_noise_level ?? '');
        $this->lightLevel = (string) ($details?->light_level ?? '');
        $this->hasHeating = (bool) $details?->has_heating;
        $this->hasAirConditioning = (bool) $details?->has_air_conditioning;
        $this->hasHotWater = (bool) $details?->has_hot_water;
        $this->hasInsects = (bool) $details?->has_insects;
        $this->hasMold = (bool) $details?->has_mold;
        $this->furnitureCondition = (string) ($details?->furniture_condition ?? '');
        $this->plumbingCondition = (string) ($details?->plumbing_condition ?? '');
        $this->kitchenCondition = (string) ($details?->kitchen_condition ?? '');
        $this->floorCondition = (string) ($details?->floor_condition ?? '');
        $this->wallsCondition = (string) ($details?->walls_condition ?? '');
        $this->lastCleanedAt = $details?->last_cleaned_at?->toDateString() ?? '';
        $this->lastRepairedAt = $details?->last_repaired_at?->toDateString() ?? '';
        $this->lastCheckedAt = $details?->last_checked_at?->toDateString() ?? '';
    }

    public function save(PropertyConditionService $conditions): void
    {
        $validated = $this->validate([
            'repairState' => ['nullable', 'string', 'max:80'],
            'cleanlinessLevel' => ['nullable', 'string', 'max:80'],
            'smellLevel' => ['nullable', 'string', 'max:80'],
            'humidityLevel' => ['nullable', 'string', 'max:80'],
            'winterTemperatureLevel' => ['nullable', 'string', 'max:80'],
            'summerTemperatureLevel' => ['nullable', 'string', 'max:80'],
            'indoorNoiseLevel' => ['nullable', 'string', 'max:80'],
            'lightLevel' => ['nullable', 'string', 'max:80'],
            'hasHeating' => ['boolean'],
            'hasAirConditioning' => ['boolean'],
            'hasHotWater' => ['boolean'],
            'hasInsects' => ['boolean'],
            'hasMold' => ['boolean'],
            'furnitureCondition' => ['nullable', 'string', 'max:80'],
            'plumbingCondition' => ['nullable', 'string', 'max:80'],
            'kitchenCondition' => ['nullable', 'string', 'max:80'],
            'floorCondition' => ['nullable', 'string', 'max:80'],
            'wallsCondition' => ['nullable', 'string', 'max:80'],
            'lastCleanedAt' => ['nullable', 'date'],
            'lastRepairedAt' => ['nullable', 'date'],
            'lastCheckedAt' => ['nullable', 'date'],
        ]);

        $conditions->updateConditionDetails($this->property(), [
            'repair_state' => $validated['repairState'] ?: null,
            'cleanliness_level' => $validated['cleanlinessLevel'] ?: null,
            'smell_level' => $validated['smellLevel'] ?: null,
            'humidity_level' => $validated['humidityLevel'] ?: null,
            'winter_temperature_level' => $validated['winterTemperatureLevel'] ?: null,
            'summer_temperature_level' => $validated['summerTemperatureLevel'] ?: null,
            'indoor_noise_level' => $validated['indoorNoiseLevel'] ?: null,
            'light_level' => $validated['lightLevel'] ?: null,
            'has_heating' => $validated['hasHeating'],
            'has_air_conditioning' => $validated['hasAirConditioning'],
            'has_hot_water' => $validated['hasHotWater'],
            'has_insects' => $validated['hasInsects'],
            'has_mold' => $validated['hasMold'],
            'furniture_condition' => $validated['furnitureCondition'] ?: null,
            'plumbing_condition' => $validated['plumbingCondition'] ?: null,
            'kitchen_condition' => $validated['kitchenCondition'] ?: null,
            'floor_condition' => $validated['floorCondition'] ?: null,
            'walls_condition' => $validated['wallsCondition'] ?: null,
            'last_cleaned_at' => $validated['lastCleanedAt'] ?: null,
            'last_repaired_at' => $validated['lastRepairedAt'] ?: null,
            'last_checked_at' => $validated['lastCheckedAt'] ?: null,
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.properties.property-condition-step');
    }
}
