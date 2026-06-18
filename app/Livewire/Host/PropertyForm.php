<?php

namespace App\Livewire\Host;

use App\Enums\PropertyType;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PropertyForm extends Component
{
    public ?Property $property = null;

    public string $title = '';

    public string $type = '';

    public string $description = '';

    public string $country = '';

    public string $city = '';

    public string $district = '';

    public string $street = '';

    public string $building = '';

    public string $apartment = '';

    public ?int $floor = null;

    public bool $hasElevator = false;

    public string $nearestTransport = '';

    public string $accessInstructions = '';

    public function mount(?Property $property = null): void
    {
        if ($property?->exists) {
            $this->property = $property;
            $this->title = $property->title;
            $this->type = $property->type?->value ?? '';
            $this->description = $property->description ?? '';
            $this->country = $property->country ?? '';
            $this->city = $property->city ?? '';
            $this->district = $property->district ?? '';
            $this->street = $property->street ?? '';
            $this->building = $property->building ?? '';
            $this->apartment = $property->apartment ?? '';
            $this->floor = $property->floor;
            $this->hasElevator = (bool) $property->has_elevator;
            $this->nearestTransport = $property->nearest_transport ?? '';
            $this->accessInstructions = $property->access_instructions ?? '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        $data = [
            'user_id' => auth()->id(),
            'title' => $this->title,
            'type' => $this->type,
            'description' => $this->description ?: null,
            'country' => $this->country,
            'city' => $this->city,
            'district' => $this->district ?: null,
            'street' => $this->street ?: null,
            'building' => $this->building ?: null,
            'apartment' => $this->apartment ?: null,
            'floor' => $this->floor,
            'has_elevator' => $this->hasElevator,
            'nearest_transport' => $this->nearestTransport ?: null,
            'access_instructions' => $this->accessInstructions ?: null,
            'status' => 'active',
        ];

        if ($this->property) {
            $this->property->update($data);
            session()->flash('success', 'Property updated.');
        } else {
            $this->property = Property::create($data);
            session()->flash('success', 'Property created.');
        }

        $this->redirect(route('host.properties.index', ['locale' => app()->getLocale()]));
    }

    public function propertyTypes(): array
    {
        return collect(PropertyType::cases())->mapWithKeys(
            fn (PropertyType $t) => [$t->value => $t->label()]
        )->all();
    }

    public function render(): View
    {
        return view('livewire.host.property-form');
    }
}
