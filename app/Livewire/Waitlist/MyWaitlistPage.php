<?php

namespace App\Livewire\Waitlist;

use App\Models\WaitlistItem;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyWaitlistPage extends Component
{
    public function cancel(int $itemId): void
    {
        WaitlistItem::query()
            ->where('id', $itemId)
            ->where('user_id', auth()->id())
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
    }

    public function render(): View
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
                'guests_count',
                'max_price_per_night',
                'max_total_price',
                'currency',
                'position',
                'last_offered_at',
                'created_at',
            ])
            ->where('user_id', auth()->id())
            ->with([
                'sleepingPlace' => fn ($query) => $query
                    ->select(['id', 'room_id', 'property_id', 'display_name', 'place_number', 'base_price_per_night', 'currency'])
                    ->with(['translations:id,sleeping_place_id,locale,title', 'property:id,district,city']),
                'activeOffer:id,waitlist_item_id,offer_expires_at,status',
            ])
            ->latest('updated_at')
            ->limit(30)
            ->get();

        return view('livewire.waitlist.my-waitlist-page', ['items' => $items]);
    }
}
