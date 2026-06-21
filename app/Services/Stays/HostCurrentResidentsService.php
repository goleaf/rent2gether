<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class HostCurrentResidentsService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getCurrentResidents(User $host, array $filters = []): CursorPaginator
    {
        return BookingStay::query()
            ->select([
                'id',
                'stay_number',
                'booking_id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in_date',
                'planned_check_out_date',
                'nights_remaining',
                'checkout_soon',
                'has_open_complaint',
                'has_payment_problem',
                'extension_requested',
                'relocation_requested',
            ])
            ->forHost($host)
            ->when(! isset($filters['status']), fn ($query) => $query->active())
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when(($filters['scope'] ?? null) === 'checkout_today', fn ($query) => $query->whereDate('planned_check_out_date', today()))
            ->when(($filters['scope'] ?? null) === 'checkout_soon', fn ($query) => $query->where('checkout_soon', true))
            ->when(($filters['scope'] ?? null) === 'complaints', fn ($query) => $query->where('has_open_complaint', true))
            ->when(($filters['scope'] ?? null) === 'payment_issue', fn ($query) => $query->where('has_payment_problem', true))
            ->when(($filters['scope'] ?? null) === 'extension_requested', fn ($query) => $query->where('extension_requested', true))
            ->when(($filters['scope'] ?? null) === 'relocation_requested', fn ($query) => $query->where('relocation_requested', true))
            ->when($filters['property_id'] ?? null, fn ($query, int $propertyId) => $query->where('property_id', $propertyId))
            ->when($filters['room_id'] ?? null, fn ($query, int $roomId) => $query->where('room_id', $roomId))
            ->with(['guest:id,name,avatar,rating_as_guest,preferred_locale', 'property:id,title', 'room:id,title', 'sleepingPlace:id,display_name,place_number'])
            ->orderBy('planned_check_out_date')
            ->orderBy('id')
            ->cursorPaginate(15);
    }

    /**
     * @return Collection<int, BookingStay>
     */
    public function getCurrentResidentsForProperty(User $host, Property $property): Collection
    {
        abort_unless((int) $property->host_user_id === (int) $host->id || (int) $property->user_id === (int) $host->id, 403);

        return BookingStay::query()
            ->forHost($host)
            ->where('property_id', $property->id)
            ->active()
            ->with(['guest:id,name,avatar', 'room:id,title', 'sleepingPlace:id,display_name,place_number'])
            ->orderBy('planned_check_out_date')
            ->get();
    }

    /**
     * @return Collection<int, BookingStay>
     */
    public function getCurrentResidentsForRoom(User $host, Room $room): Collection
    {
        $room->loadMissing('property:id,host_user_id,user_id');
        abort_unless((int) $room->property?->host_user_id === (int) $host->id || (int) $room->property?->user_id === (int) $host->id, 403);

        return BookingStay::query()
            ->forHost($host)
            ->where('room_id', $room->id)
            ->active()
            ->with(['guest:id,name,avatar', 'sleepingPlace:id,display_name,place_number'])
            ->orderBy('planned_check_out_date')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function getResidentCardData(User $host, BookingStay $stay): array
    {
        return app(StayPrivacyService::class)->filterStayForHost($host, $stay);
    }

    /**
     * @return Collection<int, BookingStay>
     */
    public function getCheckoutSoonResidents(User $host): Collection
    {
        return BookingStay::query()
            ->forHost($host)
            ->checkoutSoon()
            ->active()
            ->with(['guest:id,name,avatar', 'room:id,title', 'sleepingPlace:id,display_name,place_number'])
            ->orderBy('planned_check_out_date')
            ->get();
    }
}
