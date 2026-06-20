<?php

namespace App\Livewire\Host\Properties;

use App\Livewire\Host\Properties\Concerns\HandlesPropertyStep;
use App\Models\Property;
use App\Services\Properties\PropertyLocationService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PropertyLocationStep extends Component
{
    use HandlesPropertyStep;

    public string $nearestMetro = '';

    public string $nearestBusStop = '';

    public string $nearestShop = '';

    public string $nearestPharmacy = '';

    public string $nearestHospital = '';

    public string $nearestUniversity = '';

    public string $nearestRailwayStation = '';

    public string $nearestAirport = '';

    public ?int $distanceToCenterMeters = null;

    public ?int $walkMinutesToCenter = null;

    public ?int $transportMinutesToCenter = null;

    public string $transportConvenienceLevel = '';

    public string $districtNoiseLevel = '';

    public string $districtSafetyLevel = '';

    public string $streetLightingLevel = '';

    public bool $hasParkingNearby = false;

    public bool $hasFreeParking = false;

    public bool $hasPaidParking = false;

    public function mount(Property $property): void
    {
        $this->mountProperty($property);

        $property->loadMissing('locationDetails');
        $details = $property->locationDetails;

        $this->nearestMetro = (string) ($details?->nearest_metro ?? '');
        $this->nearestBusStop = (string) ($details?->nearest_bus_stop ?? '');
        $this->nearestShop = (string) ($details?->nearest_shop ?? $details?->nearest_supermarket ?? '');
        $this->nearestPharmacy = (string) ($details?->nearest_pharmacy ?? '');
        $this->nearestHospital = (string) ($details?->nearest_hospital ?? '');
        $this->nearestUniversity = (string) ($details?->nearest_university ?? '');
        $this->nearestRailwayStation = (string) ($details?->nearest_railway_station ?? $details?->nearest_train_station ?? '');
        $this->nearestAirport = (string) ($details?->nearest_airport ?? '');
        $this->distanceToCenterMeters = $details?->distance_to_center_meters;
        $this->walkMinutesToCenter = $details?->walk_minutes_to_center;
        $this->transportMinutesToCenter = $details?->transport_minutes_to_center;
        $this->transportConvenienceLevel = (string) ($details?->transport_convenience_level ?? '');
        $this->districtNoiseLevel = (string) ($details?->district_noise_level ?? '');
        $this->districtSafetyLevel = (string) ($details?->district_safety_level ?? '');
        $this->streetLightingLevel = (string) ($details?->street_lighting_level ?? '');
        $this->hasParkingNearby = (bool) $details?->has_parking_nearby;
        $this->hasFreeParking = (bool) $details?->has_free_parking;
        $this->hasPaidParking = (bool) $details?->has_paid_parking;
    }

    public function save(PropertyLocationService $locations): void
    {
        $validated = $this->validate([
            'nearestMetro' => ['nullable', 'string', 'max:160'],
            'nearestBusStop' => ['nullable', 'string', 'max:160'],
            'nearestShop' => ['nullable', 'string', 'max:160'],
            'nearestPharmacy' => ['nullable', 'string', 'max:160'],
            'nearestHospital' => ['nullable', 'string', 'max:160'],
            'nearestUniversity' => ['nullable', 'string', 'max:160'],
            'nearestRailwayStation' => ['nullable', 'string', 'max:160'],
            'nearestAirport' => ['nullable', 'string', 'max:160'],
            'distanceToCenterMeters' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'walkMinutesToCenter' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'transportMinutesToCenter' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'transportConvenienceLevel' => ['nullable', 'string', 'max:80'],
            'districtNoiseLevel' => ['nullable', 'string', 'max:80'],
            'districtSafetyLevel' => ['nullable', 'string', 'max:80'],
            'streetLightingLevel' => ['nullable', 'string', 'max:80'],
            'hasParkingNearby' => ['boolean'],
            'hasFreeParking' => ['boolean'],
            'hasPaidParking' => ['boolean'],
        ]);

        $locations->updateLocationDetails($this->property(), [
            'nearest_metro' => $validated['nearestMetro'] ?: null,
            'nearest_bus_stop' => $validated['nearestBusStop'] ?: null,
            'nearest_shop' => $validated['nearestShop'] ?: null,
            'nearest_pharmacy' => $validated['nearestPharmacy'] ?: null,
            'nearest_hospital' => $validated['nearestHospital'] ?: null,
            'nearest_university' => $validated['nearestUniversity'] ?: null,
            'nearest_railway_station' => $validated['nearestRailwayStation'] ?: null,
            'nearest_airport' => $validated['nearestAirport'] ?: null,
            'distance_to_center_meters' => $validated['distanceToCenterMeters'],
            'walk_minutes_to_center' => $validated['walkMinutesToCenter'],
            'transport_minutes_to_center' => $validated['transportMinutesToCenter'],
            'transport_convenience_level' => $validated['transportConvenienceLevel'] ?: null,
            'district_noise_level' => $validated['districtNoiseLevel'] ?: null,
            'district_safety_level' => $validated['districtSafetyLevel'] ?: null,
            'street_lighting_level' => $validated['streetLightingLevel'] ?: null,
            'has_parking_nearby' => $validated['hasParkingNearby'],
            'has_free_parking' => $validated['hasFreeParking'],
            'has_paid_parking' => $validated['hasPaidParking'],
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.properties.property-location-step');
    }
}
