<?php

namespace App\Services\Catalog;

use Illuminate\Support\Str;

final class AmenityRuleCatalog
{
    public const AMENITY_CATEGORIES = [
        'sleeping_place',
        'room',
        'property',
        'kitchen',
        'bathroom',
        'safety',
        'long_stay',
        'accessibility',
        'work_study',
        'transport',
        'storage',
    ];

    public const RULE_CATEGORIES = [
        'check_in_out',
        'quiet_hours',
        'smoking',
        'pets',
        'visitors',
        'kitchen',
        'bathroom',
        'cleanliness',
        'security',
        'keys',
        'alcohol_parties',
        'shared_room_behavior',
    ];

    /**
     * @return list<array{slug:string,category:string,en:string,ru:string}>
     */
    public static function amenities(): array
    {
        return [
            ['slug' => 'wifi', 'category' => 'work_study', 'en' => 'Wi-Fi', 'ru' => 'Wi-Fi'],
            ['slug' => 'fast_wifi', 'category' => 'work_study', 'en' => 'Fast Wi-Fi', 'ru' => 'Быстрый Wi-Fi'],
            ['slug' => 'kitchen', 'category' => 'kitchen', 'en' => 'Kitchen', 'ru' => 'Кухня'],
            ['slug' => 'fridge', 'category' => 'kitchen', 'en' => 'Fridge', 'ru' => 'Холодильник'],
            ['slug' => 'stove', 'category' => 'kitchen', 'en' => 'Stove', 'ru' => 'Плита'],
            ['slug' => 'oven', 'category' => 'kitchen', 'en' => 'Oven', 'ru' => 'Духовка'],
            ['slug' => 'microwave', 'category' => 'kitchen', 'en' => 'Microwave', 'ru' => 'Микроволновка'],
            ['slug' => 'kettle', 'category' => 'kitchen', 'en' => 'Kettle', 'ru' => 'Чайник'],
            ['slug' => 'dishes', 'category' => 'kitchen', 'en' => 'Dishes', 'ru' => 'Посуда'],
            ['slug' => 'washing_machine', 'category' => 'long_stay', 'en' => 'Washing machine', 'ru' => 'Стиральная машина'],
            ['slug' => 'dryer', 'category' => 'long_stay', 'en' => 'Dryer', 'ru' => 'Сушилка'],
            ['slug' => 'shower', 'category' => 'bathroom', 'en' => 'Shower', 'ru' => 'Душ'],
            ['slug' => 'bath', 'category' => 'bathroom', 'en' => 'Bath', 'ru' => 'Ванна'],
            ['slug' => 'hot_water', 'category' => 'bathroom', 'en' => 'Hot water', 'ru' => 'Горячая вода'],
            ['slug' => 'heating', 'category' => 'property', 'en' => 'Heating', 'ru' => 'Отопление'],
            ['slug' => 'air_conditioning', 'category' => 'property', 'en' => 'Air conditioning', 'ru' => 'Кондиционер'],
            ['slug' => 'fan', 'category' => 'room', 'en' => 'Fan', 'ru' => 'Вентилятор'],
            ['slug' => 'workspace', 'category' => 'work_study', 'en' => 'Workspace', 'ru' => 'Рабочее место'],
            ['slug' => 'desk', 'category' => 'work_study', 'en' => 'Desk', 'ru' => 'Стол'],
            ['slug' => 'chair', 'category' => 'work_study', 'en' => 'Chair', 'ru' => 'Стул'],
            ['slug' => 'wardrobe', 'category' => 'storage', 'en' => 'Wardrobe', 'ru' => 'Шкаф'],
            ['slug' => 'personal_locker', 'category' => 'storage', 'en' => 'Personal locker', 'ru' => 'Личный шкафчик'],
            ['slug' => 'locker_with_lock', 'category' => 'storage', 'en' => 'Locker with lock', 'ru' => 'Шкафчик с замком'],
            ['slug' => 'luggage_space', 'category' => 'storage', 'en' => 'Luggage space', 'ru' => 'Место для багажа'],
            ['slug' => 'bedding', 'category' => 'sleeping_place', 'en' => 'Bedding', 'ru' => 'Постельное бельё'],
            ['slug' => 'towel', 'category' => 'sleeping_place', 'en' => 'Towel', 'ru' => 'Полотенце'],
            ['slug' => 'pillow', 'category' => 'sleeping_place', 'en' => 'Pillow', 'ru' => 'Подушка'],
            ['slug' => 'blanket', 'category' => 'sleeping_place', 'en' => 'Blanket', 'ru' => 'Одеяло'],
            ['slug' => 'personal_lamp', 'category' => 'sleeping_place', 'en' => 'Personal lamp', 'ru' => 'Личная лампа'],
            ['slug' => 'power_socket_near_bed', 'category' => 'sleeping_place', 'en' => 'Power socket near bed', 'ru' => 'Розетка у кровати'],
            ['slug' => 'usb_charger', 'category' => 'sleeping_place', 'en' => 'USB charger', 'ru' => 'USB-зарядка'],
            ['slug' => 'curtain_for_bed', 'category' => 'sleeping_place', 'en' => 'Curtain for bed', 'ru' => 'Шторка для кровати'],
            ['slug' => 'balcony', 'category' => 'property', 'en' => 'Balcony', 'ru' => 'Балкон'],
            ['slug' => 'parking', 'category' => 'transport', 'en' => 'Parking', 'ru' => 'Парковка'],
            ['slug' => 'elevator', 'category' => 'accessibility', 'en' => 'Elevator', 'ru' => 'Лифт'],
            ['slug' => 'intercom', 'category' => 'safety', 'en' => 'Intercom', 'ru' => 'Домофон'],
            ['slug' => 'security', 'category' => 'safety', 'en' => 'Security', 'ru' => 'Охрана'],
            ['slug' => 'cctv_common_areas', 'category' => 'safety', 'en' => 'CCTV in common areas', 'ru' => 'Камеры в общих зонах'],
            ['slug' => 'first_aid_kit', 'category' => 'safety', 'en' => 'First aid kit', 'ru' => 'Аптечка'],
            ['slug' => 'fire_extinguisher', 'category' => 'safety', 'en' => 'Fire extinguisher', 'ru' => 'Огнетушитель'],
            ['slug' => 'smoke_detector', 'category' => 'safety', 'en' => 'Smoke detector', 'ru' => 'Датчик дыма'],
            ['slug' => 'gas_detector', 'category' => 'safety', 'en' => 'Gas detector', 'ru' => 'Датчик газа'],
            ['slug' => 'self_check_in', 'category' => 'property', 'en' => 'Self check-in', 'ru' => 'Самостоятельное заселение'],
            ['slug' => 'key_safe', 'category' => 'safety', 'en' => 'Key safe', 'ru' => 'Сейф для ключей'],
            ['slug' => 'electronic_lock', 'category' => 'safety', 'en' => 'Electronic lock', 'ru' => 'Электронный замок'],
            ['slug' => 'storage_shelf', 'category' => 'storage', 'en' => 'Storage shelf', 'ru' => 'Полка для хранения'],
            ['slug' => 'personal_fridge_shelf', 'category' => 'kitchen', 'en' => 'Personal fridge shelf', 'ru' => 'Личная полка в холодильнике'],
            ['slug' => 'iron', 'category' => 'long_stay', 'en' => 'Iron', 'ru' => 'Утюг'],
            ['slug' => 'hair_dryer', 'category' => 'bathroom', 'en' => 'Hair dryer', 'ru' => 'Фен'],
        ];
    }

    /**
     * @return list<array{slug:string,category:string,en:string,ru:string,requires_confirmation:bool}>
     */
    public static function rules(): array
    {
        return [
            ['slug' => 'no_smoking', 'category' => 'smoking', 'en' => 'No smoking', 'ru' => 'Не курить', 'requires_confirmation' => true],
            ['slug' => 'smoking_only_outside', 'category' => 'smoking', 'en' => 'Smoking only outside', 'ru' => 'Курить только на улице', 'requires_confirmation' => true],
            ['slug' => 'smoking_only_on_balcony', 'category' => 'smoking', 'en' => 'Smoking only on balcony', 'ru' => 'Курить только на балконе', 'requires_confirmation' => true],
            ['slug' => 'no_pets', 'category' => 'pets', 'en' => 'No pets', 'ru' => 'Без животных', 'requires_confirmation' => true],
            ['slug' => 'pets_by_request', 'category' => 'pets', 'en' => 'Pets allowed by request', 'ru' => 'Животные по согласованию', 'requires_confirmation' => true],
            ['slug' => 'no_parties', 'category' => 'alcohol_parties', 'en' => 'No parties', 'ru' => 'Без вечеринок', 'requires_confirmation' => true],
            ['slug' => 'quiet_hours_after_22', 'category' => 'quiet_hours', 'en' => 'Quiet hours after 22:00', 'ru' => 'Тихие часы после 22:00', 'requires_confirmation' => true],
            ['slug' => 'clean_dishes_after_use', 'category' => 'kitchen', 'en' => 'Clean dishes after use', 'ru' => 'Мойте посуду после использования', 'requires_confirmation' => true],
            ['slug' => 'do_not_use_other_guests_things', 'category' => 'shared_room_behavior', 'en' => "Do not use other guests' things", 'ru' => 'Не пользуйтесь вещами других гостей', 'requires_confirmation' => true],
            ['slug' => 'do_not_occupy_other_shelves', 'category' => 'shared_room_behavior', 'en' => 'Do not occupy other shelves', 'ru' => 'Не занимайте чужие полки', 'requires_confirmation' => true],
            ['slug' => 'visitors_by_agreement', 'category' => 'visitors', 'en' => 'Visitors only by agreement', 'ru' => 'Гости только по согласованию', 'requires_confirmation' => true],
            ['slug' => 'no_overnight_visitors', 'category' => 'visitors', 'en' => 'No overnight visitors', 'ru' => 'Без ночующих гостей', 'requires_confirmation' => true],
            ['slug' => 'return_keys_on_checkout', 'category' => 'keys', 'en' => 'Return keys on checkout', 'ru' => 'Верните ключи при выезде', 'requires_confirmation' => true],
            ['slug' => 'report_damage_immediately', 'category' => 'security', 'en' => 'Report damage immediately', 'ru' => 'Сразу сообщайте о поломках', 'requires_confirmation' => true],
            ['slug' => 'keep_entrance_door_locked', 'category' => 'security', 'en' => 'Keep entrance door locked', 'ru' => 'Держите входную дверь закрытой', 'requires_confirmation' => true],
            ['slug' => 'take_out_trash', 'category' => 'cleanliness', 'en' => 'Take out trash', 'ru' => 'Выносите мусор', 'requires_confirmation' => true],
            ['slug' => 'remove_shoes_inside', 'category' => 'cleanliness', 'en' => 'Remove shoes inside', 'ru' => 'Снимайте обувь в помещении', 'requires_confirmation' => true],
            ['slug' => 'no_loud_calls_at_night', 'category' => 'quiet_hours', 'en' => 'No loud calls in room at night', 'ru' => 'Без громких звонков в комнате ночью', 'requires_confirmation' => true],
            ['slug' => 'no_main_light_at_night', 'category' => 'shared_room_behavior', 'en' => 'Do not turn on main light at night', 'ru' => 'Не включайте основной свет ночью', 'requires_confirmation' => true],
            ['slug' => 'kitchen_closed_after_time', 'category' => 'kitchen', 'en' => 'Kitchen closed after certain time', 'ru' => 'Кухня закрывается после указанного времени', 'requires_confirmation' => true],
            ['slug' => 'no_washing_machine_at_night', 'category' => 'quiet_hours', 'en' => 'Washing machine not allowed at night', 'ru' => 'Нельзя пользоваться стиральной машиной ночью', 'requires_confirmation' => true],
        ];
    }

    public static function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->squish()
            ->trim()
            ->toString();
    }
}
