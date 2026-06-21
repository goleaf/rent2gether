<?php

namespace App\Livewire\Host\Pricing;

use App\Models\PromoCode;
use App\Models\SleepingPlace;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PromoCodeManager extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $code = '';

    public string $name = '';

    public float $value = 10;

    public ?string $savedMessageKey = null;

    public function mount(int|SleepingPlace $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId instanceof SleepingPlace ? $sleepingPlaceId->id : $sleepingPlaceId;
        $this->code = strtoupper(Str::random(8));
        $this->name = __('pricing.fields.promo_code');
    }

    public function save(): void
    {
        $place = SleepingPlace::query()->select(['id', 'property_id', 'user_id'])->findOrFail($this->sleepingPlaceId);

        PromoCode::query()->create([
            'code' => $this->code,
            'name' => $this->name,
            'discount_type' => PromoCode::TYPE_PROMO_CODE,
            'value_type' => PromoCode::VALUE_PERCENT,
            'value' => $this->value,
            'currency' => 'EUR',
            'sleeping_place_id' => $place->id,
            'property_id' => $place->property_id,
            'host_user_id' => $place->user_id ?: Auth::id(),
            'active' => true,
        ]);

        $this->savedMessageKey = 'pricing.messages.promo_applied';
    }

    public function render(): View
    {
        $codes = PromoCode::query()
            ->select(['id', 'code', 'name', 'value', 'active'])
            ->where('sleeping_place_id', $this->sleepingPlaceId)
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (PromoCode $code): array => [
                'code' => $code->code,
                'name' => $code->name,
                'value' => (float) $code->value,
                'status' => $code->active ? __('pricing.statuses.active') : __('pricing.statuses.inactive'),
            ])
            ->all();

        return view('livewire.host.pricing.promo-code-manager', ['codes' => $codes]);
    }
}
