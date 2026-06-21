<?php

namespace App\Livewire\Host\Pricing;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Pricing\PricingSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SleepingPlacePricingSettingsForm extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $currency = 'EUR';

    public float $baseNightlyPrice = 20;

    public ?float $weekdayPrice = null;

    public ?float $weekendPrice = null;

    public ?float $cleaningFee = null;

    public bool $depositRequired = false;

    public ?float $depositAmount = null;

    public bool $extraGuestAllowed = false;

    public int $includedGuestsCount = 1;

    public int $maxGuestsCount = 1;

    public ?float $extraGuestFee = null;

    public ?string $savedMessageKey = null;

    public function mount(int|SleepingPlace $sleepingPlaceId, PricingSettingsService $settings): void
    {
        $place = $sleepingPlaceId instanceof SleepingPlace
            ? $sleepingPlaceId
            : SleepingPlace::query()->findOrFail($sleepingPlaceId);

        $this->sleepingPlaceId = $place->id;
        $pricing = $settings->getForSleepingPlace($place);

        $this->currency = $pricing->currency;
        $this->baseNightlyPrice = (float) $pricing->base_nightly_price;
        $this->weekdayPrice = $pricing->weekday_price === null ? null : (float) $pricing->weekday_price;
        $this->weekendPrice = $pricing->weekend_price === null ? null : (float) $pricing->weekend_price;
        $this->cleaningFee = $pricing->cleaning_fee === null ? null : (float) $pricing->cleaning_fee;
        $this->depositRequired = (bool) $pricing->deposit_required;
        $this->depositAmount = $pricing->deposit_amount === null ? null : (float) $pricing->deposit_amount;
        $this->extraGuestAllowed = (bool) $pricing->extra_guest_allowed;
        $this->includedGuestsCount = (int) $pricing->included_guests_count;
        $this->maxGuestsCount = (int) $pricing->max_guests_count;
        $this->extraGuestFee = $pricing->extra_guest_fee === null ? null : (float) $pricing->extra_guest_fee;
    }

    public function save(PricingSettingsService $settings): void
    {
        $host = Auth::user();
        $place = SleepingPlace::query()->with('property')->findOrFail($this->sleepingPlaceId);

        if (! $host instanceof User) {
            abort(403);
        }

        $settings->updateForSleepingPlace($host, $place, [
            'currency' => $this->currency,
            'base_nightly_price' => $this->baseNightlyPrice,
            'weekday_price' => $this->weekdayPrice,
            'weekend_price' => $this->weekendPrice,
            'cleaning_fee' => $this->cleaningFee,
            'deposit_required' => $this->depositRequired,
            'deposit_amount' => $this->depositAmount,
            'extra_guest_allowed' => $this->extraGuestAllowed,
            'included_guests_count' => $this->includedGuestsCount,
            'max_guests_count' => $this->maxGuestsCount,
            'extra_guest_fee' => $this->extraGuestFee,
        ]);

        $this->savedMessageKey = 'pricing.messages.price_recalculated';
    }

    public function render(): View
    {
        return view('livewire.host.pricing.sleeping-place-pricing-settings-form');
    }
}
