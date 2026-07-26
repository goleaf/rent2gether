<?php

namespace App\Livewire\Host\Hints;

use App\Models\HostHintSnapshot;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostHintCard extends Component
{
    /** @var array<string, mixed> */
    private array $mountedHint = [];

    #[Locked]
    public int $hintId;

    #[Locked]
    public string $context = 'dashboard';

    #[Locked]
    public bool $showDismiss = true;

    public function mount(array $hint = [], string $context = 'dashboard', bool $showDismiss = true): void
    {
        $this->mountedHint = $hint;
        $this->hintId = (int) ($hint['id'] ?? 0);
        $this->context = $context;
        $this->showDismiss = $showDismiss;
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function hint(): array
    {
        if ($this->mountedHint !== []) {
            return $this->mountedHint;
        }

        $host = auth()->user();

        if (! $host instanceof User || $this->hintId <= 0) {
            return [];
        }

        $hint = HostHintSnapshot::query()
            ->select([
                'id',
                'user_id',
                'hint_key',
                'category',
                'type',
                'importance',
                'priority',
                'message_key',
                'message_params_json',
                'action_key',
                'action_url',
                'status',
                'source',
                'show_in_wizard',
                'show_in_dashboard',
                'show_before_publish',
                'show_on_listing_card',
            ])
            ->where('user_id', $host->id)
            ->find($this->hintId);

        return $hint instanceof HostHintSnapshot
            ? $hint->toDisplayArray(app()->getLocale())
            : [];
    }

    public function render(): View
    {
        return view('livewire.host.hints.host-hint-card', [
            'hint' => $this->hint,
        ]);
    }
}
