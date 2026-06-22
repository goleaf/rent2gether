<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class InventoryItemService
{
    public function __construct(
        private readonly InventoryNumberService $numbers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createItem(User $host, array $data): InventoryItem
    {
        $property = Property::query()->findOrFail($data['property_id']);
        $this->authorizeHostOwnsProperty($host, $property);

        $item = InventoryItem::query()->create(array_merge($data, [
            'inventory_number' => $data['inventory_number'] ?? $this->numbers->generateInventoryNumber(),
            'host_user_id' => $host->id,
            'room_id' => $data['room_id'] ?? null,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? null,
            'status' => $data['status'] ?? 'active',
            'condition_status' => $data['condition_status'] ?? 'good',
            'inventory_scope' => $data['inventory_scope'] ?? 'property',
            'current_location_type' => $data['current_location_type'] ?? 'property',
            'quantity' => $data['quantity'] ?? 1,
            'unit' => $data['unit'] ?? 'pcs',
        ]));

        app(InventoryEventService::class)->recordForItem($item, 'inventory_created', ['user_id' => $host->id]);

        return $item->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateItem(User $host, InventoryItem $item, array $data): InventoryItem
    {
        $this->authorizeHostOwnsItem($host, $item);
        $item->fill($data)->save();
        app(InventoryEventService::class)->recordForItem($item->refresh(), 'inventory_updated', ['user_id' => $host->id]);

        return $item->refresh();
    }

    public function deleteOrRetireItem(User $host, InventoryItem $item): InventoryItem
    {
        $this->authorizeHostOwnsItem($host, $item);

        return $this->markRetired($item);
    }

    public function markAvailable(InventoryItem $item): InventoryItem
    {
        return $this->transition($item, 'available');
    }

    public function markInUse(InventoryItem $item): InventoryItem
    {
        return $this->transition($item, 'in_use');
    }

    public function markNeedsCleaning(InventoryItem $item): InventoryItem
    {
        return $this->transition($item, 'needs_cleaning');
    }

    public function markNeedsWashing(InventoryItem $item): InventoryItem
    {
        return $this->transition($item, 'needs_washing');
    }

    public function markNeedsRepair(InventoryItem $item): InventoryItem
    {
        return $this->transition($item, 'needs_repair');
    }

    public function markLost(InventoryItem $item): InventoryItem
    {
        return $this->transition($item, 'lost', ['current_location_type' => 'lost']);
    }

    public function markDamaged(InventoryItem $item): InventoryItem
    {
        return $this->transition($item, 'damaged', ['condition_status' => 'damaged']);
    }

    public function markRetired(InventoryItem $item): InventoryItem
    {
        return $this->transition($item, 'retired', ['retired_at' => now()]);
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    public function getForSleepingPlace(SleepingPlace $place): Collection
    {
        return InventoryItem::query()
            ->where('sleeping_place_id', $place->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    public function getForRoom(Room $room): Collection
    {
        return InventoryItem::query()
            ->where('room_id', $room->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    public function getForProperty(Property $property): Collection
    {
        return InventoryItem::query()
            ->where('property_id', $property->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function transition(InventoryItem $item, string $status, array $extra = []): InventoryItem
    {
        $item = app(InventoryStatusService::class)->transitionItem($item, $status);

        if ($extra !== []) {
            $item->forceFill($extra)->save();
        }

        return $item->refresh();
    }

    private function authorizeHostOwnsProperty(User $host, Property $property): void
    {
        if ((int) ($property->host_user_id ?: $property->user_id) !== (int) $host->id) {
            throw new AuthorizationException(__('inventory.validation.host_must_own_property'));
        }
    }

    private function authorizeHostOwnsItem(User $host, InventoryItem $item): void
    {
        if ((int) $item->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('inventory.validation.host_must_own_inventory'));
        }
    }
}
