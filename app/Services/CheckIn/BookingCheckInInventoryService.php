<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;

class BookingCheckInInventoryService
{
    /**
     * @param  list<string>  $items
     */
    public function issueKeys(BookingCheckIn $checkIn, array $items = []): void
    {
        $checkIn->forceFill([
            'keys_handed_over' => true,
            'door_code_provided' => in_array('door_code', $items, true),
            'door_code_shared' => in_array('door_code', $items, true),
            'intercom_code_provided' => in_array('intercom_code', $items, true),
            'intercom_code_shared' => in_array('intercom_code', $items, true),
            'key_safe_code_provided' => in_array('key_safe_code', $items, true),
            'key_safe_code_shared' => in_array('key_safe_code', $items, true),
        ])->save();

        app(BookingCheckInStepService::class)->markStepCompleted($checkIn->refresh(), 'keys_handed_over');

        if (in_array('door_code', $items, true)) {
            app(BookingCheckInStepService::class)->markStepCompleted($checkIn->refresh(), 'door_code_provided');
        }
    }

    public function issueBedding(BookingCheckIn $checkIn): void
    {
        $checkIn->forceFill([
            'bedding_issued' => true,
            'bedding_given' => true,
        ])->save();

        app(BookingCheckInStepService::class)->markStepCompleted($checkIn->refresh(), 'bedding_issued');
    }

    public function issueTowel(BookingCheckIn $checkIn): void
    {
        $checkIn->forceFill([
            'towel_issued' => true,
            'towel_given' => true,
        ])->save();

        app(BookingCheckInStepService::class)->markStepCompleted($checkIn->refresh(), 'towel_issued');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assignLocker(BookingCheckIn $checkIn, array $data): void
    {
        $checkIn->forceFill([
            'locker_assigned' => true,
            'locker_given' => true,
            'locker_key_given' => (bool) ($data['key_issued'] ?? false),
        ])->save();

        app(BookingCheckInStepService::class)->markStepCompleted($checkIn->refresh(), 'locker_assigned');
    }

    /**
     * @param  list<string>  $items
     */
    public function recordIssuedInventory(BookingCheckIn $checkIn, array $items): void
    {
        if (in_array('key', $items, true) || in_array('door_code', $items, true)) {
            $this->issueKeys($checkIn, $items);
        }

        if (in_array('bedding', $items, true)) {
            $this->issueBedding($checkIn->refresh());
        }

        if (in_array('towel', $items, true)) {
            $this->issueTowel($checkIn->refresh());
        }

        if (in_array('locker', $items, true)) {
            $this->assignLocker($checkIn->refresh(), []);
        }
    }
}
