<?php

namespace App\Livewire\Host\Pricing;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceDiscountRule;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DiscountRulesEditor extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $discountType = SleepingPlaceDiscountRule::TYPE_WEEKLY;

    public string $name = '';

    public float $value = 10;

    public int $minNights = 7;

    public bool $allowStacking = false;

    public ?string $savedMessageKey = null;

    public function mount(int|SleepingPlace $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId instanceof SleepingPlace ? $sleepingPlaceId->id : $sleepingPlaceId;
        $this->name = __('pricing.discount_types.weekly');
    }

    public function save(): void
    {
        SleepingPlaceDiscountRule::query()->create([
            'sleeping_place_id' => $this->sleepingPlaceId,
            'discount_type' => $this->discountType,
            'name' => $this->name,
            'value_type' => SleepingPlaceDiscountRule::VALUE_PERCENT,
            'value' => $this->value,
            'min_nights' => $this->minNights,
            'allow_stacking' => $this->allowStacking,
            'priority' => $this->discountType === SleepingPlaceDiscountRule::TYPE_MONTHLY ? 20 : 10,
            'active' => true,
        ]);

        $this->savedMessageKey = 'pricing.messages.price_recalculated';
    }

    public function render(): View
    {
        $rules = SleepingPlaceDiscountRule::query()
            ->select(['id', 'discount_type', 'name', 'value', 'min_nights'])
            ->where('sleeping_place_id', $this->sleepingPlaceId)
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (SleepingPlaceDiscountRule $rule): array => [
                'name' => $rule->name,
                'type' => __('pricing.discount_types.'.$rule->discount_type),
                'value' => (float) $rule->value,
                'min_nights' => (int) $rule->min_nights,
            ])
            ->all();

        return view('livewire.host.pricing.discount-rules-editor', ['rules' => $rules]);
    }
}
