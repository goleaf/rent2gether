<?php

namespace App\Livewire\Hints;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Hints\HintDismissalService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DismissHintButton extends Component
{
    #[Locked]
    public ?int $sleepingPlaceId = null;

    #[Locked]
    public string $hintKey;

    #[Locked]
    public string $context = 'card';

    #[Locked]
    public bool $critical = false;

    public function mount(string $hintKey, ?int $sleepingPlaceId = null, string $context = 'card', bool $critical = false): void
    {
        $this->hintKey = $hintKey;
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->context = $context;
        $this->critical = $critical;
    }

    public function dismiss(HintDismissalService $dismissals): void
    {
        if ($this->critical && $this->context === 'before_booking') {
            $this->addError('hint', __('guest_hints.errors.critical_not_dismissible'));

            return;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            $this->addError('hint', __('guest_hints.errors.login_required'));

            return;
        }

        $place = $this->sleepingPlaceId ? SleepingPlace::query()->find($this->sleepingPlaceId) : null;
        $dismissals->dismiss($user, $this->hintKey, $place instanceof SleepingPlace ? $place : null, $this->context);
        $this->dispatch('guest-hint-dismissed', hintKey: $this->hintKey);
    }

    public function render(): View
    {
        return view('livewire.hints.dismiss-hint-button');
    }
}
