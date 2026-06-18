<?php

namespace Database\Factories;

use App\Enums\BedStatus;
use App\Enums\BedType;
use App\Enums\CancellationPolicy;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bed>
 */
class BedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'title' => 'Bed '.$this->faker->numberBetween(1, 10),
            'type' => $this->faker->randomElement(BedType::cases())->value,
            'max_guests' => 1,
            'price_per_night' => $this->faker->randomFloat(2, 8, 35),
            'cleaning_fee' => $this->faker->randomFloat(2, 0, 15),
            'deposit' => $this->faker->randomFloat(2, 0, 100),
            'discount_weekly' => 0,
            'discount_monthly' => 0,
            'min_nights' => 1,
            'instant_book' => $this->faker->boolean(60),
            'cancellation_policy' => CancellationPolicy::Flexible->value,
            'has_linen' => true,
            'has_towel' => $this->faker->boolean(),
            'has_locker' => $this->faker->boolean(40),
            'has_outlet' => $this->faker->boolean(80),
            'status' => BedStatus::Active->value,
        ];
    }
}
