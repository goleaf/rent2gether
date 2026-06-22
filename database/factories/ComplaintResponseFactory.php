<?php

namespace Database\Factories;

use App\Models\ComplaintCase;
use App\Models\ComplaintResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintResponse>
 */
class ComplaintResponseFactory extends Factory
{
    protected $model = ComplaintResponse::class;

    public function definition(): array
    {
        return [
            'complaint_case_id' => ComplaintCase::factory(),
            'user_id' => User::factory(),
            'response_type' => 'send_message',
            'message' => $this->faker->sentence(10),
            'requires_guest_response' => false,
            'requires_host_response' => false,
        ];
    }
}
