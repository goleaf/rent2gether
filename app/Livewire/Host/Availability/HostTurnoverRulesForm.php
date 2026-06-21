<?php

namespace App\Livewire\Host\Availability;

use App\Models\SleepingPlace;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostTurnoverRulesForm extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public int $minGapMinutes = 0;

    public bool $cleaningRequiredBetweenGuests = true;

    public int $cleaningGapMinutes = 0;

    public bool $inspectionRequiredAfterCheckout = false;

    public int $inspectionGapMinutes = 0;

    public bool $sameDayTurnoverAllowed = false;

    public string $earliestNewCheckInTime = '';

    public string $latestPreviousCheckOutTime = '';

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $rule = SleepingPlace::query()->findOrFail($sleepingPlaceId)->turnoverRules;

        if ($rule) {
            $this->minGapMinutes = (int) $rule->min_gap_minutes;
            $this->cleaningRequiredBetweenGuests = (bool) $rule->cleaning_required_between_guests;
            $this->cleaningGapMinutes = (int) $rule->cleaning_gap_minutes;
            $this->inspectionRequiredAfterCheckout = (bool) $rule->inspection_required_after_checkout;
            $this->inspectionGapMinutes = (int) $rule->inspection_gap_minutes;
            $this->sameDayTurnoverAllowed = (bool) $rule->same_day_turnover_allowed;
            $this->earliestNewCheckInTime = (string) $rule->earliest_new_check_in_time;
            $this->latestPreviousCheckOutTime = (string) $rule->latest_previous_check_out_time;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'minGapMinutes' => ['required', 'integer', 'min:0', 'max:2880'],
            'cleaningRequiredBetweenGuests' => ['boolean'],
            'cleaningGapMinutes' => ['required', 'integer', 'min:0', 'max:2880'],
            'inspectionRequiredAfterCheckout' => ['boolean'],
            'inspectionGapMinutes' => ['required', 'integer', 'min:0', 'max:2880'],
            'sameDayTurnoverAllowed' => ['boolean'],
            'earliestNewCheckInTime' => ['nullable', 'date_format:H:i'],
            'latestPreviousCheckOutTime' => ['nullable', 'date_format:H:i'],
        ], attributes: $this->validationAttributes());

        $place = SleepingPlace::query()->findOrFail($this->sleepingPlaceId);
        $place->turnoverRules()->updateOrCreate([], [
            'min_gap_minutes' => $validated['minGapMinutes'],
            'cleaning_required_between_guests' => $validated['cleaningRequiredBetweenGuests'],
            'cleaning_gap_minutes' => $validated['cleaningGapMinutes'],
            'inspection_required_after_checkout' => $validated['inspectionRequiredAfterCheckout'],
            'inspection_gap_minutes' => $validated['inspectionGapMinutes'],
            'same_day_turnover_allowed' => $validated['sameDayTurnoverAllowed'],
            'earliest_new_check_in_time' => $validated['earliestNewCheckInTime'] ?: null,
            'latest_previous_check_out_time' => $validated['latestPreviousCheckOutTime'] ?: null,
        ]);

        $this->dispatch('turnover-rules-saved');
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-turnover-rules-form');
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('calendar.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
