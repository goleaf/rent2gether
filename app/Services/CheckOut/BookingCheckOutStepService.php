<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutStep;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingCheckOutStepService
{
    /**
     * @return Collection<int, BookingCheckOutStep>
     */
    public function createDefaultSteps(BookingCheckOut $checkOut): Collection
    {
        foreach ($this->defaultSteps() as $index => $step) {
            BookingCheckOutStep::query()->firstOrCreate(
                [
                    'booking_check_out_id' => $checkOut->id,
                    'step_key' => $step['key'],
                ],
                [
                    'status' => $step['status'],
                    'required' => $step['required'],
                    'sort_order' => ($index + 1) * 10,
                ],
            );
        }

        return $checkOut->steps()->orderBy('sort_order')->orderBy('id')->get();
    }

    public function markStepCompleted(BookingCheckOut $checkOut, string $stepKey, ?User $user = null): BookingCheckOutStep
    {
        $this->ensureParticipant($checkOut, $user);

        $step = $this->step($checkOut, $stepKey);
        $step->forceFill([
            'status' => 'completed',
            'completed_by_user_id' => $user?->id,
            'completed_at' => now(),
        ])->save();

        $this->syncCheckOutField($checkOut, $stepKey, true);

        return $step->refresh();
    }

    public function markStepSkipped(BookingCheckOut $checkOut, string $stepKey, ?User $user = null): BookingCheckOutStep
    {
        $this->ensureParticipant($checkOut, $user);

        $step = $this->step($checkOut, $stepKey);
        $step->forceFill([
            'status' => 'skipped',
            'completed_by_user_id' => $user?->id,
            'completed_at' => now(),
        ])->save();

        return $step->refresh();
    }

    /**
     * @return Collection<int, BookingCheckOutStep>
     */
    public function getRequiredIncompleteSteps(BookingCheckOut $checkOut): Collection
    {
        return $checkOut->steps()
            ->where('required', true)
            ->whereNotIn('status', ['completed', 'skipped', 'not_required'])
            ->orderBy('sort_order')
            ->get();
    }

    private function step(BookingCheckOut $checkOut, string $stepKey): BookingCheckOutStep
    {
        $this->createDefaultSteps($checkOut);

        return BookingCheckOutStep::query()
            ->where('booking_check_out_id', $checkOut->id)
            ->where('step_key', $stepKey)
            ->firstOrFail();
    }

    private function ensureParticipant(BookingCheckOut $checkOut, ?User $user): void
    {
        if ($user === null) {
            return;
        }

        if (! in_array((int) $user->id, [(int) $checkOut->guest_user_id, (int) $checkOut->host_user_id], true)) {
            throw ValidationException::withMessages([
                'booking' => __('check_out.validation.not_participant'),
            ]);
        }
    }

    private function syncCheckOutField(BookingCheckOut $checkOut, string $stepKey, bool $value): void
    {
        $columns = [
            'keys_returned',
            'access_card_returned',
            'locker_cleared',
            'personal_items_removed',
            'bedding_returned',
            'towel_returned',
            'sleeping_place_cleared',
            'room_checked',
            'property_checked',
        ];

        if (! in_array($stepKey, $columns, true)) {
            return;
        }

        $aliases = [
            'locker_cleared' => 'locker_emptied',
            'personal_items_removed' => 'personal_items_taken',
            'sleeping_place_cleared' => 'sleeping_place_free',
        ];
        $payload = [$stepKey => $value];

        if (isset($aliases[$stepKey])) {
            $payload[$aliases[$stepKey]] = $value;
        }

        $checkOut->forceFill($payload)->save();
    }

    /**
     * @return list<array{key: string, required: bool, status: string}>
     */
    private function defaultSteps(): array
    {
        return [
            ['key' => 'guest_confirm_checkout', 'required' => true, 'status' => 'pending'],
            ['key' => 'keys_returned', 'required' => true, 'status' => 'pending'],
            ['key' => 'access_card_returned', 'required' => false, 'status' => 'pending'],
            ['key' => 'locker_cleared', 'required' => true, 'status' => 'pending'],
            ['key' => 'personal_items_removed', 'required' => true, 'status' => 'pending'],
            ['key' => 'bedding_returned', 'required' => false, 'status' => 'pending'],
            ['key' => 'towel_returned', 'required' => false, 'status' => 'pending'],
            ['key' => 'sleeping_place_cleared', 'required' => true, 'status' => 'pending'],
            ['key' => 'room_checked', 'required' => true, 'status' => 'pending'],
            ['key' => 'property_checked', 'required' => false, 'status' => 'pending'],
            ['key' => 'damage_checked', 'required' => true, 'status' => 'pending'],
            ['key' => 'inventory_checked', 'required' => true, 'status' => 'pending'],
            ['key' => 'forgotten_items_checked', 'required' => true, 'status' => 'pending'],
            ['key' => 'cleaning_created', 'required' => true, 'status' => 'pending'],
            ['key' => 'inspection_completed', 'required' => false, 'status' => 'pending'],
            ['key' => 'deposit_review_started', 'required' => false, 'status' => 'pending'],
            ['key' => 'review_requested', 'required' => false, 'status' => 'pending'],
        ];
    }
}
