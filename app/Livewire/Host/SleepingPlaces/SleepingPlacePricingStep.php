<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use App\Services\SleepingPlaces\SleepingPlacePricingService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlacePricingStep extends Component
{
    use HandlesSleepingPlaceStep;

    public ?float $basePricePerNight = null;

    public ?float $weeklyPrice = null;

    public ?float $monthlyPrice = null;

    public ?float $weekendPrice = null;

    public ?float $holidayPrice = null;

    public ?float $cleaningFee = null;

    public ?float $depositAmount = null;

    public string $currency = 'EUR';

    public ?int $minNights = null;

    public ?int $maxNights = null;

    public bool $instantBookingEnabled = false;

    public bool $requiresHostApproval = true;

    public bool $canExtend = true;

    public bool $earlyCheckInAllowed = false;

    public bool $lateCheckOutAllowed = false;

    public bool $secondGuestAllowed = false;

    public ?float $secondGuestFee = null;

    public string $cancellationPolicy = '';

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);

        $this->basePricePerNight = $sleepingPlace->base_price_per_night === null ? null : (float) $sleepingPlace->base_price_per_night;
        $this->weeklyPrice = $sleepingPlace->weekly_price === null ? null : (float) $sleepingPlace->weekly_price;
        $this->monthlyPrice = $sleepingPlace->monthly_price === null ? null : (float) $sleepingPlace->monthly_price;
        $this->weekendPrice = $sleepingPlace->weekend_price === null ? null : (float) $sleepingPlace->weekend_price;
        $this->holidayPrice = $sleepingPlace->holiday_price === null ? null : (float) $sleepingPlace->holiday_price;
        $this->cleaningFee = $sleepingPlace->cleaning_fee === null ? null : (float) $sleepingPlace->cleaning_fee;
        $this->depositAmount = $sleepingPlace->deposit_amount === null ? null : (float) $sleepingPlace->deposit_amount;
        $this->currency = $sleepingPlace->currency ?: 'EUR';
        $this->minNights = $sleepingPlace->min_nights;
        $this->maxNights = $sleepingPlace->max_nights;
        $this->instantBookingEnabled = (bool) $sleepingPlace->instant_booking_enabled;
        $this->requiresHostApproval = (bool) $sleepingPlace->requires_host_approval;
        $this->canExtend = (bool) ($sleepingPlace->can_extend ?? $sleepingPlace->extensions_allowed ?? true);
        $this->earlyCheckInAllowed = (bool) $sleepingPlace->early_check_in_allowed;
        $this->lateCheckOutAllowed = (bool) $sleepingPlace->late_check_out_allowed;
        $this->secondGuestAllowed = (bool) $sleepingPlace->second_guest_allowed;
        $this->secondGuestFee = $sleepingPlace->second_guest_fee === null ? null : (float) $sleepingPlace->second_guest_fee;
        $this->cancellationPolicy = (string) ($sleepingPlace->cancellation_policy ?? '');
    }

    public function save(SleepingPlacePricingService $service): void
    {
        $validated = $this->validate([
            'basePricePerNight' => ['required', 'numeric', 'min:0', 'max:100000'],
            'weeklyPrice' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'monthlyPrice' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'weekendPrice' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'holidayPrice' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'cleaningFee' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'depositAmount' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'currency' => ['required', 'string', 'size:3'],
            'minNights' => ['nullable', 'integer', 'min:1', 'max:365'],
            'maxNights' => ['nullable', 'integer', 'min:1', 'max:365'],
            'instantBookingEnabled' => ['boolean'],
            'requiresHostApproval' => ['boolean'],
            'canExtend' => ['boolean'],
            'earlyCheckInAllowed' => ['boolean'],
            'lateCheckOutAllowed' => ['boolean'],
            'secondGuestAllowed' => ['boolean'],
            'secondGuestFee' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'cancellationPolicy' => ['nullable', 'string', 'max:80'],
        ], attributes: __('sleeping_place.validation_attributes'));

        $service->updatePricing($this->sleepingPlace(), [
            'base_price_per_night' => $validated['basePricePerNight'],
            'weekly_price' => $validated['weeklyPrice'],
            'monthly_price' => $validated['monthlyPrice'],
            'weekend_price' => $validated['weekendPrice'],
            'holiday_price' => $validated['holidayPrice'],
            'cleaning_fee' => $validated['cleaningFee'] ?? 0,
            'deposit_amount' => $validated['depositAmount'] ?? 0,
            'currency' => strtoupper($validated['currency']),
            'min_nights' => $validated['minNights'],
            'max_nights' => $validated['maxNights'],
            'instant_booking_enabled' => $validated['instantBookingEnabled'],
            'requires_host_approval' => $validated['requiresHostApproval'],
            'extensions_allowed' => $validated['canExtend'],
            'can_extend' => $validated['canExtend'],
            'early_check_in_allowed' => $validated['earlyCheckInAllowed'],
            'late_check_out_allowed' => $validated['lateCheckOutAllowed'],
            'second_guest_allowed' => $validated['secondGuestAllowed'],
            'second_guest_fee' => $validated['secondGuestFee'],
            'cancellation_policy' => $validated['cancellationPolicy'] ?: null,
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-pricing-step');
    }
}
