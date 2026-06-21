<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShowPolicy;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookingNoShowPolicyService
{
    public function getForSleepingPlace(SleepingPlace $place): BookingNoShowPolicy
    {
        return BookingNoShowPolicy::query()
            ->where('sleeping_place_id', $place->id)
            ->where('active', true)
            ->latest('id')
            ->first()
            ?? $this->createDefaultForSleepingPlace($place);
    }

    public function createDefaultForSleepingPlace(SleepingPlace $place): BookingNoShowPolicy
    {
        return BookingNoShowPolicy::query()->create([
            'sleeping_place_id' => $place->id,
            'waiting_period_minutes' => 180,
            'same_day_waiting_period_minutes' => 60,
            'night_arrival_waiting_period_minutes' => 240,
            'hold_first_night_on_no_show' => true,
            'release_remaining_nights_after_no_show' => true,
            'refund_deposit_on_no_show' => true,
            'refund_cleaning_fee_on_no_show' => true,
            'refund_service_fee_on_no_show' => false,
            'host_payout_rule' => 'policy_based',
            'guest_penalty_rule' => 'policy_based',
            'active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateForSleepingPlace(User $host, SleepingPlace $place, array $data): BookingNoShowPolicy
    {
        $place->loadMissing('property:id,host_user_id,user_id');

        if ((int) ($place->user_id ?: $place->property?->host_user_id ?: $place->property?->user_id) !== (int) $host->id) {
            throw ValidationException::withMessages([
                'sleeping_place' => __('no_show.validation.not_host_place'),
            ]);
        }

        $validated = Validator::make($data, [
            'waiting_period_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'same_day_waiting_period_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'night_arrival_waiting_period_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'hold_first_night_on_no_show' => ['nullable', 'boolean'],
            'release_remaining_nights_after_no_show' => ['nullable', 'boolean'],
            'refund_deposit_on_no_show' => ['nullable', 'boolean'],
            'refund_cleaning_fee_on_no_show' => ['nullable', 'boolean'],
            'refund_service_fee_on_no_show' => ['nullable', 'boolean'],
            'host_payout_rule' => ['nullable', 'string', 'in:none,first_night,full_first_day,policy_based,custom'],
            'guest_penalty_rule' => ['nullable', 'string', 'in:none,first_night,policy_based,custom'],
            'active' => ['nullable', 'boolean'],
        ], [], __('no_show.validation.attributes'))->validate();

        BookingNoShowPolicy::query()
            ->where('sleeping_place_id', $place->id)
            ->where('active', true)
            ->update(['active' => false]);

        return BookingNoShowPolicy::query()->create([
            'sleeping_place_id' => $place->id,
            'waiting_period_minutes' => $validated['waiting_period_minutes'] ?? 180,
            'same_day_waiting_period_minutes' => $validated['same_day_waiting_period_minutes'] ?? 60,
            'night_arrival_waiting_period_minutes' => $validated['night_arrival_waiting_period_minutes'] ?? 240,
            'hold_first_night_on_no_show' => (bool) ($validated['hold_first_night_on_no_show'] ?? true),
            'release_remaining_nights_after_no_show' => (bool) ($validated['release_remaining_nights_after_no_show'] ?? true),
            'refund_deposit_on_no_show' => (bool) ($validated['refund_deposit_on_no_show'] ?? true),
            'refund_cleaning_fee_on_no_show' => (bool) ($validated['refund_cleaning_fee_on_no_show'] ?? true),
            'refund_service_fee_on_no_show' => (bool) ($validated['refund_service_fee_on_no_show'] ?? false),
            'host_payout_rule' => $validated['host_payout_rule'] ?? 'policy_based',
            'guest_penalty_rule' => $validated['guest_penalty_rule'] ?? 'policy_based',
            'active' => (bool) ($validated['active'] ?? true),
        ]);
    }
}
