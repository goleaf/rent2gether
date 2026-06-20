<?php

namespace App\Livewire\Host\Hints;

use App\Models\HostHintSnapshot;
use App\Models\User;
use App\Services\HostHints\HostHintDismissalService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DismissHostHintButton extends Component
{
    #[Locked]
    public int $hintId;

    #[Locked]
    public string $context = 'dashboard';

    public function mount(int $hintId, string $context = 'dashboard'): void
    {
        $this->hintId = $hintId;
        $this->context = $context;
    }

    public function dismiss(HostHintDismissalService $dismissals): void
    {
        $host = auth()->user();
        $hint = HostHintSnapshot::query()->find($this->hintId);

        if (! $host instanceof User) {
            $this->addError('hint', __('host_hints.errors.login_required'));

            return;
        }

        if (! $hint instanceof HostHintSnapshot || $hint->user_id !== $host->id) {
            $this->addError('hint', __('host_hints.errors.not_allowed'));

            return;
        }

        if ($this->context === 'before_publish' && $hint->isCriticalBeforePublish()) {
            $this->addError('hint', __('host_hints.errors.critical_not_dismissible'));

            return;
        }

        $dismissals->dismiss($host, $hint);
        $this->dispatch('host-hint-dismissed', hintId: $hint->id);
    }

    public function remindLater(HostHintDismissalService $dismissals): void
    {
        $host = auth()->user();
        $hint = HostHintSnapshot::query()->find($this->hintId);

        if ($host instanceof User && $hint instanceof HostHintSnapshot && $hint->user_id === $host->id) {
            $dismissals->remindLater($host, $hint, now()->addDays(7));
            $this->dispatch('host-hint-dismissed', hintId: $hint->id);
        }
    }

    public function render(): View
    {
        return view('livewire.host.hints.dismiss-host-hint-button');
    }
}
