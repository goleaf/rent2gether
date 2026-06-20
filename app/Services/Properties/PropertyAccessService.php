<?php

namespace App\Services\Properties;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;

class PropertyAccessService
{
    public function __construct(
        private readonly PropertyPrivacyService $privacy,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAccessDetails(Property $property, array $data): void
    {
        $property->accessDetails()->updateOrCreate(
            ['property_id' => $property->id],
            $data,
        );
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getPublicAccessSummary(Property $property): array
    {
        $property->loadMissing('accessDetails');
        $details = $property->accessDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'entrance_type' => $details->entrance_type ? __('property.entrance_types.'.$details->entrance_type) : null,
            'has_intercom' => $this->yesNo($details->has_intercom),
            'has_key' => $this->yesNo($details->has_key),
            'has_keycard' => $this->yesNo($details->has_keycard),
            'has_electronic_lock' => $this->yesNo($details->has_electronic_lock),
            'has_key_safe' => $this->yesNo($details->has_key_safe),
            'self_check_in_available' => $details->self_check_in_available ? __('property.values.self_check_in') : $this->yesNo($details->self_check_in_available),
            'meet_host_required' => $this->yesNo($details->meet_host_required),
            'meet_host_representative_required' => $this->yesNo($details->meet_host_representative_required),
            'access_24_7' => $this->yesNo($details->access_24_7),
            'can_return_at_night' => $this->yesNo($details->can_return_at_night),
            'has_night_entry_restrictions' => $this->yesNo($details->has_night_entry_restrictions),
            'delivery_allowed' => $this->yesNo($details->delivery_allowed),
        ]);
    }

    public function getConfirmedBookingAccessInstructions(Property $property, Booking $booking): string
    {
        $property->loadMissing(['accessDetails', 'translations']);
        $guest = $booking->guest;
        $details = $property->accessDetails;

        if (! $details || ! $guest instanceof User) {
            return __('property.privacy.access_after_booking');
        }

        $parts = array_filter([
            $details->self_check_in_available ? __('property.values.self_check_in') : null,
            $details->entrance_type ? __('property.entrance_types.'.$details->entrance_type) : null,
            $this->privacy->canShowKeySafeLocation($guest, $property, $booking) ? $details->key_safe_location_note : null,
            $this->privacy->canShowDoorCode($guest, $property, $booking) ? $details->what_if_code_fails : null,
            $details->what_if_key_does_not_work,
        ]);

        return $parts === []
            ? __('property.privacy.access_after_booking')
            : implode("\n", $parts);
    }

    public function canShowEntryCodes(User $user, Property $property, ?Booking $booking = null): bool
    {
        return $this->privacy->canShowDoorCode($user, $property, $booking);
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

            $rows[] = [
                'label' => __('property.fields.'.$field),
                'value' => $value,
            ];
        }

        return $rows;
    }

    private function yesNo(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? __('property.values.yes') : __('property.values.no');
    }
}
