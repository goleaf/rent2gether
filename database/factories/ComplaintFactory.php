<?php

namespace Database\Factories;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'type' => $this->faker->randomElement(ComplaintType::cases())->value,
            'description' => $this->faker->paragraph(),
            'urgency' => 'normal',
            'status' => ComplaintStatus::Open->value,
        ];
    }
}
