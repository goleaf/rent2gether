<?php

namespace App\Livewire\Search;

use App\Models\Bed;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BedCard extends Component
{
    #[Locked]
    public int $bedId;

    #[Locked]
    public int $nights = 0;

    public function mount(int|Bed $bed, int $nights = 0): void
    {
        $this->bedId = $bed instanceof Bed ? $bed->id : $bed;
        $this->nights = $nights;
    }

    #[Computed]
    public function bed(): Bed
    {
        return Bed::query()
            ->select([
                'id',
                'room_id',
                'title',
                'type',
                'price_per_night',
                'cleaning_fee',
                'deposit',
                'discount_weekly',
                'discount_monthly',
                'instant_book',
                'has_locker',
                'cancellation_policy',
            ])
            ->with([
                'room:id,property_id,title,gender_type',
                'room.property:id,city,district',
                'room.property.cardMedia:id,mediable_id,mediable_type,disk,path,thumb_path,thumbnail_path,mobile_path,full_path,alt_text,is_primary,is_cover,sort_order',
                'room.property.cardMedia.translations:id,media_item_id,locale,caption',
            ])
            ->findOrFail($this->bedId);
    }

    #[Computed]
    public function priceSummary(): ?array
    {
        if ($this->nights <= 0) {
            return null;
        }

        return $this->bed->calculatePrice(request('in', now()->toDateString()), request('out', now()->addDays($this->nights)->toDateString()));
    }

    public function render(): View
    {
        $bed = $this->bed;
        $priceSummary = $this->priceSummary;

        return view('livewire.search.bed-card', [
            'bed' => $bed,
            'media' => $bed->room->property->cardMedia,
            'nightlyPrice' => Number::currency((float) $bed->price_per_night, 'EUR', app()->getLocale()),
            'totalPrice' => $priceSummary === null ? null : Number::currency((float) $priceSummary['total'], 'EUR', app()->getLocale()),
        ]);
    }
}
