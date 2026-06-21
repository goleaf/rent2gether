<?php

namespace Database\Factories;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationConsent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationConsent>
 */
class BookingRelocationConsentFactory extends Factory
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
            'user_id' => User::factory(),
            'consent_type' => 'guest_accepts_new_place',
            'status' => 'pending',
            'message' => null,
            'responded_at' => null,
        ];
    }
}
