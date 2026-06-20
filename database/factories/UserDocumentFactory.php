<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDocument>
 */
class UserDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_type' => 'identity_document',
            'status' => 'pending',
            'file_path' => 'private/documents/document.jpg',
            'encrypted' => true,
            'uploaded_at' => now(),
            'verified_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ];
    }
}
