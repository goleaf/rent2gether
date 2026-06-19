<?php

namespace App\Livewire\Host;

use App\Enums\GenderType;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RoomForm extends Component
{
    #[Locked]
    public Property $property;

    public ?Room $room = null;

    public string $title = '';

    public string $genderType = 'mixed';

    public string $description = '';

    public int $capacity = 4;

    public ?float $areaSqm = null;

    public bool $hasLock = false;

    public bool $hasWindow = true;

    public bool $hasWardrobe = false;

    public bool $hasDesk = false;

    public bool $hasAc = false;

    public bool $hasHeating = true;

    public bool $hasBalcony = false;

    public function mount(Property $property, ?Room $room = null): void
    {
        $this->property = $property;

        if ($room?->exists) {
            $this->room = $room;
            $this->title = $room->title;
            $this->genderType = $room->gender_type?->value ?? 'mixed';
            $this->description = $room->description ?? '';
            $this->capacity = $room->capacity ?? 4;
            $this->areaSqm = $room->area_sqm;
            $this->hasLock = (bool) $room->has_lock;
            $this->hasWindow = (bool) $room->has_window;
            $this->hasWardrobe = (bool) $room->has_wardrobe;
            $this->hasDesk = (bool) $room->has_desk;
            $this->hasAc = (bool) $room->has_ac;
            $this->hasHeating = (bool) $room->has_heating;
            $this->hasBalcony = (bool) $room->has_balcony;
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $data = [
            'property_id' => $this->property->id,
            'title' => $this->title,
            'gender_type' => $this->genderType,
            'description' => $this->description ?: null,
            'capacity' => $this->capacity,
            'area_sqm' => $this->areaSqm,
            'has_lock' => $this->hasLock,
            'has_window' => $this->hasWindow,
            'has_wardrobe' => $this->hasWardrobe,
            'has_desk' => $this->hasDesk,
            'has_ac' => $this->hasAc,
            'has_heating' => $this->hasHeating,
            'has_balcony' => $this->hasBalcony,
            'status' => 'active',
        ];

        if ($this->room) {
            $this->room->update($data);
            session()->flash('success', __('notifications.flash.room_updated'));
        } else {
            Room::create($data);
            session()->flash('success', __('notifications.flash.room_created'));
        }

        $this->redirect(route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $this->property]));
    }

    public function genderTypes(): array
    {
        return collect(GenderType::cases())->mapWithKeys(
            fn (GenderType $g) => [$g->value => $g->label()]
        )->all();
    }

    public function render(): View
    {
        return view('livewire.host.room-form');
    }
}
