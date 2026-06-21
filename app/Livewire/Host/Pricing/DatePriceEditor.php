<?php

namespace App\Livewire\Host\Pricing;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceDatePrice;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DatePriceEditor extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $date = '';

    public float $price = 0;

    public string $priceType = SleepingPlaceDatePrice::TYPE_MANUAL_OVERRIDE;

    public ?string $savedMessageKey = null;

    public function mount(int|SleepingPlace $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId instanceof SleepingPlace ? $sleepingPlaceId->id : $sleepingPlaceId;
        $this->date = now()->addWeek()->toDateString();
    }

    public function save(): void
    {
        SleepingPlaceDatePrice::query()->create([
            'sleeping_place_id' => $this->sleepingPlaceId,
            'date' => $this->date,
            'price' => $this->price,
            'currency' => 'EUR',
            'price_type' => $this->priceType,
            'check_in_allowed' => true,
            'check_out_allowed' => true,
        ]);

        $this->savedMessageKey = 'pricing.messages.price_recalculated';
    }

    public function render(): View
    {
        $prices = SleepingPlaceDatePrice::query()
            ->select(['id', 'date', 'price', 'currency', 'price_type'])
            ->where('sleeping_place_id', $this->sleepingPlaceId)
            ->orderByDesc('date')
            ->limit(8)
            ->get()
            ->map(fn (SleepingPlaceDatePrice $price): array => [
                'date' => $price->date->translatedFormat('d M'),
                'price' => $price->price.' '.$price->currency,
                'type' => __('pricing.price_types.'.$price->price_type),
            ])
            ->all();

        return view('livewire.host.pricing.date-price-editor', ['prices' => $prices]);
    }
}
