<?php

namespace App\Livewire\Waitlist;

use App\Data\Listings\ListingCardContext;
use App\Models\SleepingPlace;
use App\Models\WaitlistItem;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class WaitlistItemCard extends Component
{
    #[Locked]
    public int $waitlistItemId;

    public function mount(int $waitlistItemId): void
    {
        $this->waitlistItemId = $waitlistItemId;
    }

    public function render(): View
    {
        $item = WaitlistItem::query()
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
            ])
            ->findOrFail($this->waitlistItemId);
        $context = new ListingCardContext(
            userId: (int) $item->user_id,
            locale: app()->getLocale(),
            currency: strtoupper($item->currency ?: 'EUR'),
            checkInDate: $item->desired_check_in_date?->toDateString(),
            checkOutDate: $item->desired_check_out_date?->toDateString(),
            nightsCount: $item->nights_count ? (int) $item->nights_count : null,
            guestsCount: max(1, (int) ($item->guests_count ?: 1)),
            source: 'waitlist',
            filters: [
                'variant' => 'waitlist',
                'waitlist_ids' => [(int) $item->sleeping_place_id],
            ],
        );
        $place = app(ListingCardQueryService::class)
            ->forComparison([(int) $item->sleeping_place_id], $context)
            ->first();
        $listingCard = $place instanceof SleepingPlace
            ? app(ListingCardService::class)->build($place, $context)->toArray()
            : null;

        return view('livewire.waitlist.waitlist-item-card', [
            'item' => $item,
            'listingCard' => $listingCard,
        ]);
    }
}
