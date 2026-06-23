<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Queries\Stays\CurrentResidentsForHostQuery;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class HostCurrentResidentsService
{
    public function __construct(private readonly CurrentResidentsForHostQuery $currentResidentsForHost) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getCurrentResidents(User $host, array $filters = []): CursorPaginator
    {
        return $this->currentResidentsForHost
            ->handle($host, $filters)
            ->cursorPaginate(15);
    }

    /**
     * @return Collection<int, BookingStay>
     */
    public function getCurrentResidentsForProperty(User $host, Property $property): Collection
    {
        abort_unless((int) $property->host_user_id === (int) $host->id || (int) $property->user_id === (int) $host->id, 403);

        return $this->currentResidentsForHost
            ->handle($host, ['property_id' => $property->id])
            ->get();
    }

    /**
     * @return Collection<int, BookingStay>
     */
    public function getCurrentResidentsForRoom(User $host, Room $room): Collection
    {
        $room->loadMissing('property:id,host_user_id,user_id');
        abort_unless((int) $room->property?->host_user_id === (int) $host->id || (int) $room->property?->user_id === (int) $host->id, 403);

        return $this->currentResidentsForHost
            ->handle($host, ['room_id' => $room->id])
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
        return $this->currentResidentsForHost
            ->handle($host)
            ->checkoutSoon()
            ->get();
    }
}
