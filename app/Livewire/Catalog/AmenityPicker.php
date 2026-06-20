<?php

namespace App\Livewire\Catalog;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\Catalog\AmenityRuleLookupService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class AmenityPicker extends Component
{
    /** @var list<int> */
    #[Modelable]
    public array $selectedIds = [];

    public string $search = '';

    public string $context = 'property';

    public ?string $ownerType = null;

    public ?int $ownerId = null;

    public int $limit = 80;

    /**
     * @param  list<int|string>  $selectedIds
     */
    public function mount(
        array $selectedIds = [],
        string $context = 'property',
        ?string $ownerType = null,
        ?int $ownerId = null,
        int $limit = 80,
    ): void {
        $this->context = $context;
        $this->ownerType = $ownerType;
        $this->ownerId = $ownerId;
        $this->limit = $limit;
        $this->selectedIds = $this->integerIds($selectedIds);

        if ($this->ownerType && $this->ownerId) {
            $this->selectedIds = $this->ownerModel()
                ->amenities()
                ->select(['amenities.id'])
                ->pluck('amenities.id')
                ->map(fn (int $id): int => $id)
                ->all();
        }
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr($this->search, 0, 80);
    }

    public function toggle(int $amenityId): void
    {
        if (! Amenity::query()->visible()->whereKey($amenityId)->exists()) {
            return;
        }

        $selected = collect($this->selectedIds)->map(fn (int|string $id): int => (int) $id);
        $this->selectedIds = $selected->contains($amenityId)
            ? $selected->reject(fn (int $id): bool => $id === $amenityId)->values()->all()
            : $selected->push($amenityId)->unique()->values()->all();

        $this->syncOwner();
        $this->dispatch('amenities-updated', ids: $this->selectedIds);
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function isSelected(int $amenityId): bool
    {
        return in_array($amenityId, $this->integerIds($this->selectedIds), true);
    }

    /**
     * @return list<array{category:string,category_label:string,options:list<array{id:int,slug:string,category:string,label:string,description:?string,selected:bool}>}>
     */
    #[Computed]
    public function groups(): array
    {
        $selected = $this->integerIds($this->selectedIds);

        return collect(app(AmenityRuleLookupService::class)->amenityGroups(
            locale: app()->getLocale(),
            search: $this->search,
            categories: $this->categoriesForContext(),
            limit: $this->limit,
        ))
            ->map(fn (array $group): array => [
                ...$group,
                'options' => collect($group['options'])
                    ->map(fn (array $option): array => [
                        ...$option,
                        'selected' => in_array((int) $option['id'], $selected, true),
                    ])
                    ->all(),
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.catalog.amenity-picker');
    }

    /**
     * @return list<string>
     */
    private function categoriesForContext(): array
    {
        return match ($this->context) {
            'sleeping_place' => ['sleeping_place', 'storage', 'accessibility', 'work_study'],
            'room' => ['room', 'sleeping_place', 'storage', 'work_study', 'accessibility', 'safety'],
            'property' => ['property', 'kitchen', 'bathroom', 'safety', 'long_stay', 'accessibility', 'transport', 'storage', 'work_study'],
            default => [],
        };
    }

    private function syncOwner(): void
    {
        if (! $this->ownerType || ! $this->ownerId) {
            return;
        }

        $this->ownerModel()->amenities()->sync($this->integerIds($this->selectedIds));
    }

    private function ownerModel(): Property|Room|SleepingPlace
    {
        return match ($this->ownerType) {
            'property' => $this->ownedProperty(),
            'room' => $this->ownedRoom(),
            'sleeping_place' => $this->ownedSleepingPlace(),
            default => abort(404),
        };
    }

    private function ownedProperty(): Property
    {
        $property = Property::query()
            ->select(['id', 'host_user_id', 'user_id'])
            ->findOrFail($this->ownerId);

        abort_unless(auth()->check() && $property->isOwnedBy(auth()->user()), 403);

        return $property;
    }

    private function ownedRoom(): Room
    {
        $room = Room::query()
            ->select(['id', 'property_id'])
            ->with(['property:id,host_user_id,user_id'])
            ->findOrFail($this->ownerId);

        abort_unless(auth()->check() && $room->property?->isOwnedBy(auth()->user()), 403);

        return $room;
    }

    private function ownedSleepingPlace(): SleepingPlace
    {
        $sleepingPlace = SleepingPlace::query()
            ->select(['id', 'property_id'])
            ->with(['property:id,host_user_id,user_id'])
            ->findOrFail($this->ownerId);

        abort_unless(auth()->check() && $sleepingPlace->property?->isOwnedBy(auth()->user()), 403);

        return $sleepingPlace;
    }

    /**
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    private function integerIds(array $ids): array
    {
        return collect($ids)
            ->map(fn (int|string $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
