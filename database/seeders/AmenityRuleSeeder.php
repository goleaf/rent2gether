<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Rule;
use Illuminate\Database\Seeder;

class AmenityRuleSeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['slug' => 'wifi', 'name_normalized' => 'wifi', 'en' => 'Wi-Fi', 'ru' => 'Wi-Fi'],
            ['slug' => 'kitchen', 'name_normalized' => 'kitchen', 'en' => 'Kitchen', 'ru' => 'Кухня'],
            ['slug' => 'locker', 'name_normalized' => 'locker', 'en' => 'Locker', 'ru' => 'Шкафчик'],
        ];

        foreach ($amenities as $item) {
            $amenity = Amenity::query()->firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name_normalized' => $item['name_normalized'],
                    'category' => 'comfort',
                    'status' => 'active',
                ]
            );

            $amenity->translations()->firstOrCreate(['locale' => 'en'], [
                'name' => $item['en'],
                'name_normalized' => $item['name_normalized'],
            ]);
            $amenity->translations()->firstOrCreate(['locale' => 'ru'], [
                'name' => $item['ru'],
                'name_normalized' => $item['name_normalized'],
            ]);
        }

        $rules = [
            ['slug' => 'no_smoking', 'name_normalized' => 'no smoking', 'en' => 'No smoking', 'ru' => 'Не курить'],
            ['slug' => 'quiet_hours', 'name_normalized' => 'quiet hours', 'en' => 'Quiet hours', 'ru' => 'Тихие часы'],
        ];

        foreach ($rules as $item) {
            $rule = Rule::query()->firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name_normalized' => $item['name_normalized'],
                    'category' => 'house',
                    'requires_confirmation' => true,
                    'status' => 'active',
                ]
            );

            $rule->translations()->firstOrCreate(['locale' => 'en'], [
                'name' => $item['en'],
                'name_normalized' => $item['name_normalized'],
            ]);
            $rule->translations()->firstOrCreate(['locale' => 'ru'], [
                'name' => $item['ru'],
                'name_normalized' => $item['name_normalized'],
            ]);
        }
    }
}
