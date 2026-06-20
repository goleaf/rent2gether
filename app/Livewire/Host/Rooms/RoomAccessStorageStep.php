<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use App\Services\Rooms\RoomAccessService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomAccessStorageStep extends Component
{
    use HandlesRoomStep;

    public bool $hasDoor = true;

    public bool $hasLock = false;

    public bool $hasKey = false;

    public bool $keyGivenToGuest = false;

    public bool $canLockFromInside = false;

    public bool $canLockFromOutside = false;

    public string $privacyLevel = '';

    public bool $hasWardrobe = false;

    public bool $hasSharedWardrobe = false;

    public bool $hasPersonalLockers = false;

    public ?int $personalLockersCount = null;

    public bool $lockersHaveLocks = false;

    public bool $hasLuggageSpace = false;

    public bool $hasDesk = false;

    public bool $hasChairs = false;

    public ?int $chairsCount = null;

    public bool $hasMirror = false;

    public bool $canStoreFood = false;

    public string $foodStorageAllowedType = '';

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
        $room->loadMissing('accessDetails');
        $details = $room->accessDetails;

        $this->hasDoor = (bool) ($details?->has_door ?? true);
        $this->hasLock = (bool) ($details?->has_lock ?? $room->has_lock);
        $this->hasKey = (bool) $details?->has_key;
        $this->keyGivenToGuest = (bool) $details?->key_given_to_guest;
        $this->canLockFromInside = (bool) $details?->can_lock_from_inside;
        $this->canLockFromOutside = (bool) $details?->can_lock_from_outside;
        $this->privacyLevel = (string) ($details?->privacy_level ?? '');
        $this->hasWardrobe = (bool) ($details?->has_wardrobe ?? $room->has_wardrobe);
        $this->hasSharedWardrobe = (bool) $details?->has_shared_wardrobe;
        $this->hasPersonalLockers = (bool) $details?->has_personal_lockers;
        $this->personalLockersCount = $details?->personal_lockers_count;
        $this->lockersHaveLocks = (bool) $details?->lockers_have_locks;
        $this->hasLuggageSpace = (bool) $details?->has_luggage_space;
        $this->hasDesk = (bool) ($details?->has_desk ?? $room->has_desk);
        $this->hasChairs = (bool) ($details?->has_chairs ?? $room->has_chair);
        $this->chairsCount = $details?->chairs_count;
        $this->hasMirror = (bool) ($details?->has_mirror ?? $room->has_mirror);
        $this->canStoreFood = (bool) $details?->can_store_food;
        $this->foodStorageAllowedType = (string) ($details?->food_storage_allowed_type ?? '');
    }

    public function save(RoomAccessService $access): void
    {
        $validated = $this->validate([
            'hasDoor' => ['boolean'],
            'hasLock' => ['boolean'],
            'hasKey' => ['boolean'],
            'keyGivenToGuest' => ['boolean'],
            'canLockFromInside' => ['boolean'],
            'canLockFromOutside' => ['boolean'],
            'privacyLevel' => ['nullable', 'string', 'max:80'],
            'hasWardrobe' => ['boolean'],
            'hasSharedWardrobe' => ['boolean'],
            'hasPersonalLockers' => ['boolean'],
            'personalLockersCount' => ['nullable', 'integer', 'min:0', 'max:200'],
            'lockersHaveLocks' => ['boolean'],
            'hasLuggageSpace' => ['boolean'],
            'hasDesk' => ['boolean'],
            'hasChairs' => ['boolean'],
            'chairsCount' => ['nullable', 'integer', 'min:0', 'max:200'],
            'hasMirror' => ['boolean'],
            'canStoreFood' => ['boolean'],
            'foodStorageAllowedType' => ['nullable', 'string', 'max:80'],
        ], attributes: __('room.validation_attributes'));

        $room = $this->room();
        $access->updateAccessDetails($room, [
            'has_door' => $validated['hasDoor'],
            'has_lock' => $validated['hasLock'],
            'has_key' => $validated['hasKey'],
            'key_given_to_guest' => $validated['keyGivenToGuest'],
            'can_lock_from_inside' => $validated['canLockFromInside'],
            'can_lock_from_outside' => $validated['canLockFromOutside'],
            'privacy_level' => $validated['privacyLevel'] ?: null,
            'has_wardrobe' => $validated['hasWardrobe'],
            'has_shared_wardrobe' => $validated['hasSharedWardrobe'],
            'has_personal_lockers' => $validated['hasPersonalLockers'],
            'personal_lockers_count' => $validated['personalLockersCount'],
            'lockers_have_locks' => $validated['lockersHaveLocks'],
            'has_luggage_space' => $validated['hasLuggageSpace'],
            'has_desk' => $validated['hasDesk'],
            'has_chairs' => $validated['hasChairs'],
            'chairs_count' => $validated['chairsCount'],
            'has_mirror' => $validated['hasMirror'],
            'can_store_food' => $validated['canStoreFood'],
            'food_storage_allowed_type' => $validated['foodStorageAllowedType'] ?: null,
        ]);

        $room->update([
            'has_lock' => $validated['hasLock'],
            'has_wardrobe' => $validated['hasWardrobe'],
            'has_desk' => $validated['hasDesk'],
            'has_chair' => $validated['hasChairs'],
            'has_mirror' => $validated['hasMirror'],
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-access-storage-step');
    }
}
