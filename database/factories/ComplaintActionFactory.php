<?php

namespace Database\Factories;

use App\Models\ComplaintAction;
use App\Models\ComplaintCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintAction>
 */
class ComplaintActionFactory extends Factory
{
    protected $model = ComplaintAction::class;

    public function definition(): array
    {
        return [
            'complaint_case_id' => ComplaintCase::factory(),
            'action_type' => 'notify_other_party',
            'status' => 'pending',
            'due_at' => now()->addDay(),
        ];
    }
}
