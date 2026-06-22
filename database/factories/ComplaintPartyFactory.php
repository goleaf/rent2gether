<?php

namespace Database\Factories;

use App\Models\ComplaintCase;
use App\Models\ComplaintParty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintParty>
 */
class ComplaintPartyFactory extends Factory
{
    protected $model = ComplaintParty::class;

    public function definition(): array
    {
        return [
            'complaint_case_id' => ComplaintCase::factory(),
            'user_id' => User::factory(),
            'party_type' => 'reporter',
            'display_name_snapshot' => $this->faker->name(),
            'role_in_case' => 'reported_problem',
            'can_respond' => true,
        ];
    }
}
