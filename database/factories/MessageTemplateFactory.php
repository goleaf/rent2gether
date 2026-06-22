<?php

namespace Database\Factories;

use App\Models\MessageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MessageTemplate>
 */
class MessageTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_key' => 'template_'.Str::lower(Str::random(10)),
            'template_category' => 'booking',
            'sender_type' => 'guest',
            'conversation_type' => 'booking',
            'title_translation_key' => 'messages.templates_guest.i_will_arrive_soon',
            'body_translation_key' => 'messages.templates_guest.i_will_arrive_soon',
            'visible_to_guest' => true,
            'visible_to_host' => false,
            'requires_booking' => false,
            'requires_check_in' => false,
            'requires_check_out' => false,
            'requires_active_stay' => false,
            'creates_action' => false,
            'action_type' => 'none',
            'sort_order' => 0,
            'active' => true,
        ];
    }
}
