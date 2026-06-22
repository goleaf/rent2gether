<?php

namespace Database\Factories;

use App\Models\InventoryIssue;
use App\Models\InventoryIssueMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryIssueMedia>
 */
class InventoryIssueMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_issue_id' => InventoryIssue::factory(),
            'media_type' => 'photo',
            'media_role' => 'issue_evidence',
            'path' => sprintf('inventory/issues/%s.jpg', fake()->uuid()),
            'visibility' => 'guest_and_host',
        ];
    }
}
