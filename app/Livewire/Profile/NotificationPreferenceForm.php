<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NotificationPreferenceForm extends Component
{
    #[Validate('required|string|max:50')]
    public string $category = 'bookings';

    #[Validate('required|string|max:50')]
    public string $channel = 'in_app';

    public bool $enabled = true;

    public function save(): void
    {
        $this->validate();
        $user = Auth::user();

        if ($user instanceof User) {
            UserNotificationPreference::query()->updateOrCreate(
                ['user_id' => $user->id, 'category' => $this->category, 'channel' => $this->channel],
                ['enabled' => $this->enabled],
            );
        }
    }

    public function render(): View
    {
        return view('livewire.profile.notification-preference-form');
    }
}
