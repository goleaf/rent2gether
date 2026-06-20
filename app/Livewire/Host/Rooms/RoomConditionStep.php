<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use App\Services\Rooms\RoomConditionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomConditionStep extends Component
{
    use HandlesRoomStep;

    public string $conditionState = '';

    public string $repairState = '';

    public string $cleanlinessLevel = '';

    public string $floorCondition = '';

    public string $wallsCondition = '';

    public string $ceilingCondition = '';

    public string $windowCondition = '';

    public string $doorCondition = '';

    public string $lockCondition = '';

    public string $furnitureCondition = '';

    public bool $hasDust = false;

    public bool $hasBadSmell = false;

    public bool $hasDampMarks = false;

    public bool $hasMold = false;

    public bool $hasInsects = false;

    public bool $hasDamage = false;

    public bool $needsRepair = false;

    public bool $recentlyRenovated = false;

    public string $lastCleanedAt = '';

    public string $lastCheckedAt = '';

    public string $lastRepairedAt = '';

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
        $room->loadMissing('conditionDetails');
        $details = $room->conditionDetails;

        $this->conditionState = (string) ($details?->condition_state ?? '');
        $this->repairState = (string) ($details?->repair_state ?? '');
        $this->cleanlinessLevel = (string) ($details?->cleanliness_level ?? '');
        $this->floorCondition = (string) ($details?->floor_condition ?? '');
        $this->wallsCondition = (string) ($details?->walls_condition ?? '');
        $this->ceilingCondition = (string) ($details?->ceiling_condition ?? '');
        $this->windowCondition = (string) ($details?->window_condition ?? '');
        $this->doorCondition = (string) ($details?->door_condition ?? '');
        $this->lockCondition = (string) ($details?->lock_condition ?? '');
        $this->furnitureCondition = (string) ($details?->furniture_condition ?? '');
        $this->hasDust = (bool) $details?->has_dust;
        $this->hasBadSmell = (bool) $details?->has_bad_smell;
        $this->hasDampMarks = (bool) $details?->has_damp_marks;
        $this->hasMold = (bool) $details?->has_mold;
        $this->hasInsects = (bool) $details?->has_insects;
        $this->hasDamage = (bool) $details?->has_damage;
        $this->needsRepair = (bool) $details?->needs_repair;
        $this->recentlyRenovated = (bool) $details?->recently_renovated;
        $this->lastCleanedAt = (string) $details?->last_cleaned_at?->toDateString();
        $this->lastCheckedAt = (string) $details?->last_checked_at?->toDateString();
        $this->lastRepairedAt = (string) $details?->last_repaired_at?->toDateString();
    }

    public function save(RoomConditionService $conditions): void
    {
        $validated = $this->validate([
            'conditionState' => ['nullable', 'string', 'max:80'],
            'repairState' => ['nullable', 'string', 'max:80'],
            'cleanlinessLevel' => ['nullable', 'string', 'max:80'],
            'floorCondition' => ['nullable', 'string', 'max:80'],
            'wallsCondition' => ['nullable', 'string', 'max:80'],
            'ceilingCondition' => ['nullable', 'string', 'max:80'],
            'windowCondition' => ['nullable', 'string', 'max:80'],
            'doorCondition' => ['nullable', 'string', 'max:80'],
            'lockCondition' => ['nullable', 'string', 'max:80'],
            'furnitureCondition' => ['nullable', 'string', 'max:80'],
            'hasDust' => ['boolean'],
            'hasBadSmell' => ['boolean'],
            'hasDampMarks' => ['boolean'],
            'hasMold' => ['boolean'],
            'hasInsects' => ['boolean'],
            'hasDamage' => ['boolean'],
            'needsRepair' => ['boolean'],
            'recentlyRenovated' => ['boolean'],
            'lastCleanedAt' => ['nullable', 'date'],
            'lastCheckedAt' => ['nullable', 'date'],
            'lastRepairedAt' => ['nullable', 'date'],
        ], attributes: __('room.validation_attributes'));

        $room = $this->room();
        $conditions->updateConditionDetails($room, [
            'condition_state' => $validated['conditionState'] ?: null,
            'repair_state' => $validated['repairState'] ?: null,
            'cleanliness_level' => $validated['cleanlinessLevel'] ?: null,
            'floor_condition' => $validated['floorCondition'] ?: null,
            'walls_condition' => $validated['wallsCondition'] ?: null,
            'ceiling_condition' => $validated['ceilingCondition'] ?: null,
            'window_condition' => $validated['windowCondition'] ?: null,
            'door_condition' => $validated['doorCondition'] ?: null,
            'lock_condition' => $validated['lockCondition'] ?: null,
            'furniture_condition' => $validated['furnitureCondition'] ?: null,
            'has_dust' => $validated['hasDust'],
            'has_bad_smell' => $validated['hasBadSmell'],
            'has_damp_marks' => $validated['hasDampMarks'],
            'has_mold' => $validated['hasMold'],
            'has_insects' => $validated['hasInsects'],
            'has_damage' => $validated['hasDamage'],
            'needs_repair' => $validated['needsRepair'],
            'recently_renovated' => $validated['recentlyRenovated'],
            'last_cleaned_at' => $validated['lastCleanedAt'] ?: null,
            'last_checked_at' => $validated['lastCheckedAt'] ?: null,
            'last_repaired_at' => $validated['lastRepairedAt'] ?: null,
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-condition-step');
    }
}
