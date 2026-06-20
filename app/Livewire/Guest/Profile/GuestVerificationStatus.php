<?php

namespace App\Livewire\Guest\Profile;

use App\Models\User;
use App\Services\Users\UserVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GuestVerificationStatus extends Component
{
    /**
     * @return array<string, string>
     */
    public function statuses(UserVerificationService $verifications): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        return collect(['email', 'phone', 'identity'])
            ->map(fn (string $type): array => [
                'type' => $type,
                'type_label' => $this->typeLabel($type),
                'status' => $verifications->getVerificationStatus($user, $type),
                'status_label' => $this->statusLabel($verifications->getVerificationStatus($user, $type)),
            ])
            ->values()
            ->all();
    }

    public function render(UserVerificationService $verifications): View
    {
        return view('livewire.guest.profile.guest-verification-status', [
            'statuses' => $this->statuses($verifications),
        ]);
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'email' => __('verifications.types.email'),
            'phone' => __('verifications.types.phone'),
            'identity' => __('verifications.types.identity'),
            default => __('verifications.types.document'),
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'required' => __('verifications.statuses.required'),
            'pending' => __('verifications.statuses.pending'),
            'verified' => __('verifications.statuses.verified'),
            'rejected' => __('verifications.statuses.rejected'),
            'expired' => __('verifications.statuses.expired'),
            'failed' => __('verifications.statuses.failed'),
            default => __('verifications.statuses.not_required'),
        };
    }
}
