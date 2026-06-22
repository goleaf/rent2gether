<?php

namespace Database\Factories;

use App\Models\DisputeCase;
use App\Models\DisputeEvidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisputeEvidence>
 */
class DisputeEvidenceFactory extends Factory
{
    protected $model = DisputeEvidence::class;

    public function definition(): array
    {
        return [
            'dispute_case_id' => DisputeCase::factory(),
            'uploaded_by_user_id' => User::factory(),
            'evidence_type' => 'photo',
            'media_type' => 'photo',
            'evidence_role' => 'problem_photo',
            'path' => 'disputes/'.$this->faker->uuid().'.jpg',
            'visibility' => 'guest_and_host',
        ];
    }
}
