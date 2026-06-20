<?php

namespace Database\Factories;

use App\Models\HostCleaningTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCleaningTemplate>
 */
class HostCleaningTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Default cleaning checklist',
            'cleaning_type' => 'after_check_out',
            'target_type' => 'sleeping_place',
            'items_json' => [
                ['item_key' => 'replace_bedding', 'required' => true],
                ['item_key' => 'upload_after_photos', 'required' => true],
            ],
            'is_default' => false,
        ];
    }
}
