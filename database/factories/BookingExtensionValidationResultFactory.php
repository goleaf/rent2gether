<?php

namespace Database\Factories;

use App\Models\BookingExtension;
use App\Models\BookingExtensionValidationResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingExtensionValidationResult>
 */
class BookingExtensionValidationResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_extension_id' => BookingExtension::factory(),
            'validation_key' => 'host_confirmation_required',
            'severity' => 'warning',
            'message_key' => 'booking_extensions.validation.host_confirmation_required',
            'message_params_json' => [],
            'blocking' => false,
            'visible_to_guest' => true,
            'visible_to_host' => true,
        ];
    }
}
