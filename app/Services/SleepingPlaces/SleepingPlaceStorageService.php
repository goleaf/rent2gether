<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;

class SleepingPlaceStorageService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateStorageDetails(SleepingPlace $place, array $data): void
    {
        $place->storageDetails()->updateOrCreate(['sleeping_place_id' => $place->id], $data);

        $place->update(array_filter([
            'has_locker' => $data['has_personal_locker'] ?? null,
            'locker_has_lock' => $data['locker_has_lock'] ?? null,
            'has_lockable_locker' => $data['has_lockable_locker'] ?? null,
            'has_luggage_space' => $data['has_luggage_space'] ?? null,
        ], fn (mixed $value): bool => $value !== null));
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getStorageSummary(SleepingPlace $place): array
    {
        $place->loadMissing('storageDetails');
        $details = $place->storageDetails;

        if (! $details) {
            return [];
        }

        $lockerValue = $details->has_personal_locker
            ? ($details->locker_has_lock ? __('sleeping_place.values.personal_locker_with_lock') : __('sleeping_place.values.personal_locker'))
            : null;

        return $this->rows([
            'has_luggage_space' => $this->yesNo($details->has_luggage_space),
            'has_shoe_space' => $this->yesNo($details->has_shoe_space),
            'has_personal_locker' => $lockerValue,
            'locker_has_lock' => $this->yesNo($details->locker_has_lock),
            'can_store_valuables' => $this->yesNo($details->can_store_valuables),
            'can_store_documents' => $this->yesNo($details->can_store_documents),
            'can_store_laptop' => $this->yesNo($details->can_store_laptop),
            'locker_size' => $details->locker_size ? __('sleeping_place.locker_sizes.'.$details->locker_size) : null,
            'can_leave_luggage_before_checkin' => $this->yesNo($details->can_leave_luggage_before_checkin),
            'can_leave_luggage_after_checkout' => $this->yesNo($details->can_leave_luggage_after_checkout),
            'storage_responsibility_note' => $details->storage_responsibility_note,
        ]);
    }

    public function canStoreValuables(SleepingPlace $place): bool
    {
        $place->loadMissing('storageDetails');
        $details = $place->storageDetails;

        return (bool) ($details?->has_personal_locker && $details?->locker_has_lock && $details?->can_store_valuables);
    }

    /**
     * @return list<string>
     */
    public function getStorageWarnings(SleepingPlace $place): array
    {
        $place->loadMissing('storageDetails');
        $details = $place->storageDetails;

        if (! $details) {
            return [];
        }

        return array_values(array_filter([
            $details->guest_should_bring_lock ? __('sleeping_place.warnings.bring_lock') : null,
            $details->can_store_valuables === false ? __('sleeping_place.warnings.valuables') : null,
        ]));
    }

    /**
     * @param  array<string, ?string>  $values
     * @return list<array{label:string,value:string}>
     */
    private function rows(array $values): array
    {
        $rows = [];

        foreach ($values as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $rows[] = ['label' => __('sleeping_place.fields.'.$field), 'value' => $value];
        }

        return $rows;
    }

    private function yesNo(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? __('sleeping_place.values.yes') : __('sleeping_place.values.no');
    }
}
