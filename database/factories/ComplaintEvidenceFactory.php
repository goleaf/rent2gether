<?php

namespace Database\Factories;

use App\Models\ComplaintCase;
use App\Models\ComplaintEvidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintEvidence>
 */
class ComplaintEvidenceFactory extends Factory
{
    protected $model = ComplaintEvidence::class;

    public function definition(): array
    {
        return [
            'complaint_case_id' => ComplaintCase::factory(),
            'uploaded_by_user_id' => User::factory(),
            'evidence_type' => 'photo',
            'media_type' => 'photo',
            'evidence_role' => 'problem_photo',
            'path' => 'complaints/'.$this->faker->uuid().'.jpg',
            'visibility' => 'guest_and_host',
        ];
    }
}
