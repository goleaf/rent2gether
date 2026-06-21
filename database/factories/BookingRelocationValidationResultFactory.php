<?php

namespace Database\Factories;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationValidationResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationValidationResult>
 */
class BookingRelocationValidationResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_relocation_id' => BookingRelocation::factory(),
            'validation_key' => 'guest_consent_required',
            'severity' => 'warning',
            'message_key' => 'booking_relocations.validation.guest_consent_required',
            'message_params_json' => null,
            'blocking' => false,
            'visible_to_guest' => true,
            'visible_to_host' => true,
        ];
    }
}
