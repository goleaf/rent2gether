<?php

namespace App\Services\Waitlist;

use App\Models\SleepingPlace;
use App\Models\WaitlistItem;
use Illuminate\Support\Collection;

class WaitlistHostViewService
{
    /**
     * @return array{total:int,ready_to_book_count:int,average_max_price:float|null,items:Collection<int, WaitlistItem>}
     */
    public function summaryForPlace(SleepingPlace $place): array
    {
        $items = WaitlistItem::query()
            ->select([
                'id',
                'user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'desired_check_in_date',
                'desired_check_out_date',
                'nights_count',
                'max_price_per_night',
                'ready_to_book_immediately',
                'position',
                'added_at',
            ])
            ->with(['user:id,name,rating_as_guest,phone_verified,identity_verified,avatar'])
            ->forSleepingPlace($place)
            ->whereIn('status', ['active', 'waiting', 'offered'])
            ->orderedQueue()
            ->limit(20)
            ->get();

        return [
            'total' => $items->count(),
            'ready_to_book_count' => $items->where('ready_to_book_immediately', true)->count(),
            'average_max_price' => $items->whereNotNull('max_price_per_night')->avg('max_price_per_night'),
            'items' => $items,
        ];
    }
}
