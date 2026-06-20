<?php

namespace App\Services;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserVerification;

class UserVerificationService
{
    public function requireVerification(User $user, string $type): UserVerification
    {
        return $this->store($user, $type, ['status' => 'required']);
    }

    public function markVerified(User $user, string $type): UserVerification
    {
        $verification = $this->store($user, $type, [
            'status' => 'verified',
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->syncUserVerificationColumns($user, $type);

        return $verification;
    }

    public function markRejected(User $user, string $type, string $reason): UserVerification
    {
        return $this->store($user, $type, [
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_at' => null,
        ]);
    }

    public function getVerificationStatus(User $user, string $type): string
    {
        return (string) (UserVerification::query()
            ->where('user_id', $user->id)
            ->where('verification_type', $type)
            ->value('status') ?? 'not_required');
    }

    public function isReadyForBooking(User $user, SleepingPlace $place): bool
    {
        unset($place);

        return ! in_array($this->getVerificationStatus($user, 'phone'), ['required', 'pending', 'rejected', 'failed'], true)
            && ! in_array($this->getVerificationStatus($user, 'identity'), ['required', 'pending', 'rejected', 'failed'], true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function store(User $user, string $type, array $payload): UserVerification
    {
        return UserVerification::query()->updateOrCreate(
            ['user_id' => $user->id, 'verification_type' => $type],
            $payload,
        );
    }

    private function syncUserVerificationColumns(User $user, string $type): void
    {
        $payload = match ($type) {
            'email' => ['email_verified_at' => now()],
            'phone' => ['phone_verified' => true, 'phone_verified_at' => now()],
            'identity' => ['identity_verified' => true, 'identity_verified_at' => now()],
            default => [],
        };

        if ($payload !== []) {
            $user->forceFill($payload)->save();
        }
    }
}
