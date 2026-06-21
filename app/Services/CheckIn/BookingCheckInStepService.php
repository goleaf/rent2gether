<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInStep;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingCheckInStepService
{
    /**
     * @return Collection<int, BookingCheckInStep>
     */
    public function createDefaultSteps(BookingCheckIn $checkIn): Collection
    {
        foreach ($this->defaultSteps() as $sortOrder => $step) {
            BookingCheckInStep::query()->firstOrCreate(
                [
                    'booking_check_in_id' => $checkIn->id,
                    'step_key' => $step['key'],
                ],
                [
                    'status' => $step['required'] ? 'pending' : 'not_required',
                    'required' => $step['required'],
                    'sort_order' => $sortOrder + 1,
                ],
            );
        }

        return $checkIn->steps()->orderBy('sort_order')->get();
    }

    public function markStepCompleted(BookingCheckIn $checkIn, string $stepKey, ?User $user = null): BookingCheckInStep
    {
        $this->ensureParticipant($user, $checkIn);

        $step = $this->step($checkIn, $stepKey);
        $step->forceFill([
            'status' => 'completed',
            'completed_by_user_id' => $user?->id,
            'completed_at' => now(),
        ])->save();

        $this->syncCheckInField($checkIn, $stepKey, true);

        return $step->refresh();
    }

    public function markStepSkipped(BookingCheckIn $checkIn, string $stepKey, ?User $user = null): BookingCheckInStep
    {
        $this->ensureParticipant($user, $checkIn);

        $step = $this->step($checkIn, $stepKey);
        $step->forceFill([
            'status' => 'skipped',
            'completed_by_user_id' => $user?->id,
            'completed_at' => now(),
        ])->save();

        return $step->refresh();
    }

    /**
     * @return Collection<int, BookingCheckInStep>
     */
    public function getRequiredIncompleteSteps(BookingCheckIn $checkIn): Collection
    {
        return $checkIn->steps()
            ->where('required', true)
            ->where('status', '!=', 'completed')
            ->orderBy('sort_order')
            ->get();
    }

    private function step(BookingCheckIn $checkIn, string $stepKey): BookingCheckInStep
    {
        return BookingCheckInStep::query()->firstOrCreate(
            [
                'booking_check_in_id' => $checkIn->id,
                'step_key' => $stepKey,
            ],
            [
                'status' => 'pending',
                'required' => true,
                'sort_order' => 100,
            ],
        );
    }

    private function ensureParticipant(?User $user, BookingCheckIn $checkIn): void
    {
        if ($user === null) {
            return;
        }

        if (! in_array((int) $user->id, [(int) $checkIn->guest_user_id, (int) $checkIn->host_user_id], true)) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_participant'),
            ]);
        }
    }

    private function syncCheckInField(BookingCheckIn $checkIn, string $stepKey, bool $value): void
    {
        $aliases = [
            'show_instruction' => 'instructions_shown_at',
            'show_address' => 'address_shown_at',
            'guest_on_the_way' => 'guest_on_the_way_at',
            'guest_arrived' => 'guest_arrived_at',
            'keys_handed_over' => 'keys_handed_over',
            'door_code_provided' => 'door_code_provided',
            'room_shown' => 'room_shown',
            'sleeping_place_shown' => 'sleeping_place_shown',
            'rules_explained' => 'rules_explained',
            'bedding_issued' => 'bedding_issued',
            'towel_issued' => 'towel_issued',
            'locker_assigned' => 'locker_assigned',
            'guest_confirmed' => 'guest_confirmed_at',
            'host_confirmed' => 'host_confirmed_at',
        ];

        $column = $aliases[$stepKey] ?? null;

        if ($column === null) {
            return;
        }

        $checkIn->forceFill([
            $column => str_ends_with($column, '_at') ? ($value ? now() : null) : $value,
        ])->save();

        $legacy = [
            'bedding_issued' => 'bedding_given',
            'towel_issued' => 'towel_given',
            'locker_assigned' => 'locker_given',
            'door_code_provided' => 'door_code_shared',
        ];

        if (isset($legacy[$stepKey])) {
            $checkIn->forceFill([$legacy[$stepKey] => $value])->save();
        }
    }

    /**
     * @return list<array{key:string, required:bool}>
     */
    private function defaultSteps(): array
    {
        return [
            ['key' => 'show_instruction', 'required' => true],
            ['key' => 'show_address', 'required' => true],
            ['key' => 'guest_on_the_way', 'required' => false],
            ['key' => 'guest_arrived', 'required' => true],
            ['key' => 'keys_handed_over', 'required' => true],
            ['key' => 'door_code_provided', 'required' => false],
            ['key' => 'room_shown', 'required' => true],
            ['key' => 'sleeping_place_shown', 'required' => true],
            ['key' => 'rules_explained', 'required' => true],
            ['key' => 'bedding_issued', 'required' => true],
            ['key' => 'towel_issued', 'required' => false],
            ['key' => 'locker_assigned', 'required' => false],
            ['key' => 'guest_confirmed', 'required' => true],
            ['key' => 'host_confirmed', 'required' => true],
        ];
    }
}
