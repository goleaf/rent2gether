<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationValidationResult;
use Illuminate\Support\Collection;

class BookingRelocationValidationService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateNewSleepingPlace(BookingRelocation $relocation): Collection
    {
        $results = collect();

        if (! $relocation->new_sleeping_place_id || (int) $relocation->new_sleeping_place_id === (int) $relocation->current_sleeping_place_id) {
            $results->push($this->result('new_sleeping_place_required'));
        }

        return $results;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateGuestEligibility(BookingRelocation $relocation): Collection
    {
        return collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateGuestCount(BookingRelocation $relocation): Collection
    {
        return collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateRoomRules(BookingRelocation $relocation): Collection
    {
        return collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validatePriceDifferenceConsent(BookingRelocation $relocation): Collection
    {
        return $relocation->price_difference_amount != 0
            ? collect([$this->result('price_difference_requires_guest_consent', blocking: false, severity: 'warning')])
            : collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateHostConsent(BookingRelocation $relocation): Collection
    {
        return $relocation->requires_host_consent
            ? collect([$this->result('host_consent_required', blocking: false, severity: 'warning')])
            : collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateGuestConsent(BookingRelocation $relocation): Collection
    {
        return $relocation->requires_guest_consent
            ? collect([$this->result('guest_consent_required', blocking: false, severity: 'warning')])
            : collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateOldPlaceAfterMove(BookingRelocation $relocation): Collection
    {
        return collect([
            $this->result('old_place_needs_inspection', blocking: false, severity: 'info'),
            $this->result('inventory_transfer_required', blocking: false, severity: 'info'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createValidationResult(BookingRelocation $relocation, array $data): BookingRelocationValidationResult
    {
        return BookingRelocationValidationResult::query()->create([
            'booking_relocation_id' => $relocation->id,
            'validation_key' => $data['validation_key'],
            'severity' => $data['severity'] ?? 'error',
            'message_key' => $data['message_key'] ?? 'booking_relocations.validation.'.$data['validation_key'],
            'message_params_json' => $data['message_params_json'] ?? null,
            'blocking' => $data['blocking'] ?? true,
            'visible_to_guest' => $data['visible_to_guest'] ?? true,
            'visible_to_host' => $data['visible_to_host'] ?? true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function result(string $key, bool $blocking = true, string $severity = 'error'): array
    {
        return [
            'validation_key' => $key,
            'severity' => $severity,
            'message_key' => 'booking_relocations.validation.'.$key,
            'blocking' => $blocking,
            'visible_to_guest' => true,
            'visible_to_host' => true,
        ];
    }
}
