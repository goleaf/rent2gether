<?php

namespace Database\Factories;

use App\Models\NotificationReminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationReminder>
 */
class NotificationReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reminder_number' => 'REM-'.now()->format('Y').'-'.$this->faker->unique()->numberBetween(100000, 999999),
            'user_id' => User::factory(),
            'recipient_type' => 'guest',
            'reminder_type' => $this->faker->randomElement(['payment_deadline', 'check_in_soon', 'checkout_soon']),
            'status' => 'scheduled',
            'priority' => 'normal',
            'source_type' => null,
            'source_id' => null,
            'scheduled_for' => now()->addHour(),
            'due_at' => null,
            'processed_at' => null,
            'cancelled_at' => null,
            'expired_at' => null,
            'translation_params_json' => ['reference' => 'RTG-DEMO'],
            'action_type' => 'open_booking',
            'action_url' => null,
        ];
    }
}
