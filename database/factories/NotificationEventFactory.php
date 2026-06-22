<?php

namespace Database\Factories;

use App\Models\NotificationEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationEvent>
 */
class NotificationEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventKey = $this->faker->randomElement(['booking_confirmed', 'payment_required', 'guest_sent_message']);

        return [
            'event_number' => 'NEVT-'.now()->format('Y').'-'.$this->faker->unique()->numberBetween(100000, 999999),
            'event_key' => $eventKey,
            'event_type' => 'system',
            'notification_category' => $eventKey === 'payment_required' ? 'payment' : 'booking',
            'source_type' => 'system',
            'source_id' => null,
            'created_by_user_id' => User::factory(),
            'payload_json' => ['seeded' => true],
        ];
    }
}
