<?php

namespace App\Livewire\Catalog;

use App\Models\Property;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Services\Catalog\AmenityRuleLookupService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class RulePicker extends Component
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
                ->rules()
                ->select(['rules.id'])
                ->pluck('rules.id')
                ->map(fn (int $id): int => $id)
                ->all();
        }
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr($this->search, 0, 80);
    }

    public function toggle(int $ruleId): void
    {
        if (! Rule::query()->visible()->whereKey($ruleId)->exists()) {
            return;
        }

        $selected = collect($this->selectedIds)->map(fn (int|string $id): int => (int) $id);
        $this->selectedIds = $selected->contains($ruleId)
            ? $selected->reject(fn (int $id): bool => $id === $ruleId)->values()->all()
            : $selected->push($ruleId)->unique()->values()->all();

        $this->syncOwner();
        $this->dispatch('rules-updated', ids: $this->selectedIds);
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function isSelected(int $ruleId): bool
    {
        return in_array($ruleId, $this->integerIds($this->selectedIds), true);
    }

    /**
     * @return list<array{category:string,category_label:string,options:list<array{id:int,slug:string,category:string,label:string,description:?string,selected:bool}>}>
     */
    #[Computed]
    public function groups(): array
    {
        $selected = $this->integerIds($this->selectedIds);

        return collect(app(AmenityRuleLookupService::class)->ruleGroups(
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
        return view('livewire.catalog.rule-picker');
    }

    /**
     * @return list<string>
     */
    private function categoriesForContext(): array
    {
        return match ($this->context) {
            'sleeping_place' => ['quiet_hours', 'shared_room_behavior', 'security', 'keys'],
            'room' => ['quiet_hours', 'shared_room_behavior', 'cleanliness', 'visitors', 'security'],
            'property' => ['check_in_out', 'quiet_hours', 'smoking', 'pets', 'visitors', 'kitchen', 'bathroom', 'cleanliness', 'security', 'keys', 'alcohol_parties', 'shared_room_behavior'],
            default => [],
        };
    }

    private function syncOwner(): void
    {
        if (! $this->ownerType || ! $this->ownerId) {
            return;
        }

        $this->ownerModel()->rules()->sync($this->integerIds($this->selectedIds));
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
