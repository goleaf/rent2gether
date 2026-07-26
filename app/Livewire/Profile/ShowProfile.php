<?php

namespace App\Livewire\Profile;

use App\Enums\ReviewType;
use App\Models\User;
use App\Services\Privacy\PublicProfileVisibility;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowProfile extends Component
{
    #[Locked]
    public int $userId;

    public function mount(User $user): void
    {
        $this->userId = $user->id;
    }

    #[Computed]
    public function user(): User
    {
        return User::query()
            ->select([
                'id',
                'name',
                'phone',
                'phone_verified',
                'email_verified_at',
                'avatar',
                'avatar_path',
                'date_of_birth',
                'city',
                'languages',
                'bio',
                'occupation',
                'identity_verified',
                'identity_verified_at',
                'is_host',
                'rating_as_guest',
                'rating_as_host',
                'completed_stays_count',
                'hosted_stays_count',
                'created_at',
            ])
            ->with([
                'profile:id,user_id,display_name,avatar_path,city_id,occupation,languages_json,phone,email_verified_at,phone_verified_at,identity_verified_at',
                'profile.city:id,name',
                'setting:id,user_id,privacy_preferences_json',
            ])
            ->findOrFail($this->userId);
    }

    #[Computed]
    public function profileVisibility(): array
    {
        return app(PublicProfileVisibility::class)->profileFor($this->user, auth()->user());
    }

    #[Computed]
    public function reviewsAsGuest()
    {
        if (! $this->profileVisibility['show_reviews']) {
            return collect();
        }

        return $this->user->reviewsReceived()
            ->select(['id', 'reviewer_id', 'reviewee_id', 'type', 'overall_rating', 'liked_text', 'comment', 'positive_comment', 'created_at', 'status'])
            ->where('type', ReviewType::HostToGuest->value)
            ->visible()
            ->with('reviewer:id,name')
            ->latest('created_at')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function reviewsAsHost()
    {
        if (! $this->profileVisibility['show_reviews']) {
            return collect();
        }

        return $this->user->reviewsReceived()
            ->select(['id', 'reviewer_id', 'reviewee_id', 'type', 'overall_rating', 'liked_text', 'comment', 'positive_comment', 'created_at', 'status'])
            ->where('type', ReviewType::GuestToPlace->value)
            ->visible()
            ->with('reviewer:id,name')
            ->latest('created_at')
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.profile.show-profile', [
            'user' => $this->user,
        ]);
    }
}
