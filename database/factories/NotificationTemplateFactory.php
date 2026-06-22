<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = 'demo_'.$this->faker->unique()->slug(2);

        return [
            'template_key' => $key,
            'notification_category' => $this->faker->randomElement(['booking', 'payment', 'message', 'system']),
            'title_translation_key' => 'notifications.events.'.$key,
            'body_translation_key' => 'notifications.events.'.$key,
            'short_body_translation_key' => null,
            'default_priority' => $this->faker->randomElement(['low', 'normal', 'high']),
            'default_action_type' => 'open_booking',
            'supports_in_app' => true,
            'supports_email' => $this->faker->boolean(40),
            'supports_sms_future' => false,
            'supports_push_future' => false,
            'supports_conversation_event' => false,
            'requires_booking' => false,
            'requires_action' => false,
            'is_critical' => false,
            'active' => true,
        ];
    }
}
