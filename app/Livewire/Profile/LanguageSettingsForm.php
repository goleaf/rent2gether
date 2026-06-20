<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Services\Users\UserLanguageService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LanguageSettingsForm extends Component
{
    #[Validate('required|string|max:10')]
    public string $languageCode = 'en';

    #[Validate('required|in:native,fluent,intermediate,basic')]
    public string $level = 'basic';

    public bool $isPrimary = false;

    public function save(UserLanguageService $languages): void
    {
        $this->validate();
        $user = Auth::user();

        if ($user instanceof User) {
            $languages->add($user, $this->languageCode, $this->level, $this->isPrimary);
        }
    }

    public function render(): View
    {
        return view('livewire.profile.language-settings-form');
    }
}
