<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\NotificationDigest;
use App\Models\NotificationDigestItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDigestItem>
 */
class NotificationDigestItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_digest_id' => NotificationDigest::factory(),
            'notification_id' => Notification::factory(),
            'sort_order' => $this->faker->numberBetween(0, 50),
        ];
    }
}
