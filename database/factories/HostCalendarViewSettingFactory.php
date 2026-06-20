<?php

namespace Database\Factories;

use App\Models\HostCalendarViewSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCalendarViewSetting>
 */
class HostCalendarViewSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'default_view' => 'property',
            'default_property_id' => null,
            'default_room_id' => null,
            'show_prices' => true,
            'show_guest_names' => true,
            'show_cleaning' => true,
            'show_repairs' => true,
            'show_payouts' => true,
            'show_occupancy' => true,
            'compact_mode' => true,
        ];
    }
}
