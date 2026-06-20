<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use App\Services\Rooms\RoomLayoutService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomLayoutStep extends Component
{
    use HandlesRoomStep;

    public ?float $area = null;

    public ?float $lengthMeters = null;

    public ?float $widthMeters = null;

    public ?float $ceilingHeightMeters = null;

    public ?int $windowsCount = null;

    public string $windowSize = '';

    public string $windowView = '';

    public string $cardinalDirection = '';

    public bool $hasBalcony = false;

    public bool $balconyAccessible = false;

    public bool $hasFreePassageSpace = false;

    public bool $narrowPassages = false;

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
        $room->loadMissing('layoutDetails');
        $details = $room->layoutDetails;

        $this->area = $details?->area === null ? null : (float) $details->area;
        $this->lengthMeters = $details?->length_meters === null ? null : (float) $details->length_meters;
        $this->widthMeters = $details?->width_meters === null ? null : (float) $details->width_meters;
        $this->ceilingHeightMeters = $details?->ceiling_height_meters === null ? null : (float) $details->ceiling_height_meters;
        $this->windowsCount = $details?->windows_count ?? $room->windows_count;
        $this->windowSize = (string) ($details?->window_size ?? '');
        $this->windowView = (string) ($details?->window_view ?? $room->window_view ?? '');
        $this->cardinalDirection = (string) ($details?->cardinal_direction ?? '');
        $this->hasBalcony = (bool) ($details?->has_balcony ?? $room->has_balcony);
        $this->balconyAccessible = (bool) $details?->balcony_accessible;
        $this->hasFreePassageSpace = (bool) $details?->has_free_passage_space;
        $this->narrowPassages = (bool) $details?->narrow_passages;
    }

    public function save(RoomLayoutService $layouts): void
    {
        $validated = $this->validate([
            'area' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'lengthMeters' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'widthMeters' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ceilingHeightMeters' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'windowsCount' => ['nullable', 'integer', 'min:0', 'max:20'],
            'windowSize' => ['nullable', 'string', 'max:80'],
            'windowView' => ['nullable', 'string', 'max:160'],
            'cardinalDirection' => ['nullable', 'string', 'max:40'],
            'hasBalcony' => ['boolean'],
            'balconyAccessible' => ['boolean'],
            'hasFreePassageSpace' => ['boolean'],
            'narrowPassages' => ['boolean'],
        ], attributes: __('room.validation_attributes'));

        $room = $this->room();
        $layouts->updateLayoutDetails($room, [
            'area' => $validated['area'],
            'length_meters' => $validated['lengthMeters'],
            'width_meters' => $validated['widthMeters'],
            'ceiling_height_meters' => $validated['ceilingHeightMeters'],
            'windows_count' => $validated['windowsCount'],
            'window_size' => $validated['windowSize'] ?: null,
            'window_view' => $validated['windowView'] ?: null,
            'cardinal_direction' => $validated['cardinalDirection'] ?: null,
            'has_balcony' => $validated['hasBalcony'],
            'balcony_accessible' => $validated['balconyAccessible'],
            'has_free_passage_space' => $validated['hasFreePassageSpace'],
            'narrow_passages' => $validated['narrowPassages'],
        ]);

        $room->update([
            'area' => $validated['area'],
            'windows_count' => $validated['windowsCount'] ?? 0,
            'window_view' => $validated['windowView'] ?: null,
            'has_balcony' => $validated['hasBalcony'],
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-layout-step');
    }
}
