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
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
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
        return view('livewire.profile.show-profile');
    }
}
