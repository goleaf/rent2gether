<?php

namespace App\Livewire\Favorites;

use App\Models\Favorite;
use App\Models\User;
use App\Services\Favorites\FavoriteReminderService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FavoriteReminderSheet extends Component
{
    public int $favoriteId;

    public string $remindAt = '';

    public string $reminderText = '';

    public function mount(int $favoriteId): void
    {
        $favorite = Favorite::query()
            ->select(['id', 'user_id', 'remind_at', 'reminder_text'])
            ->where('user_id', auth()->id())
            ->findOrFail($favoriteId);

        $this->favoriteId = $favorite->id;
        $this->remindAt = $favorite->remind_at?->format('Y-m-d\TH:i') ?: now()->addDay()->format('Y-m-d\TH:i');
        $this->reminderText = (string) $favorite->reminder_text;
    }

    public function choose(string $option): void
    {
        $date = match ($option) {
            'tomorrow' => CarbonImmutable::tomorrow()->setTime(9, 0),
            'three_days' => CarbonImmutable::now()->addDays(3)->setTime(9, 0),
            'week' => CarbonImmutable::now()->addWeek()->setTime(9, 0),
            default => CarbonImmutable::now()->addDay()->setTime(9, 0),
        };

        $this->remindAt = $date->format('Y-m-d\TH:i');
    }

    public function save(FavoriteReminderService $reminders): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $this->validate([
            'remindAt' => ['required', 'date', 'after:now'],
            'reminderText' => ['nullable', 'string', 'max:1000'],
        ], attributes: [
            'remindAt' => __('favorites.fields.remind_at'),
            'reminderText' => __('favorites.fields.reminder_text'),
        ]);

        $favorite = Favorite::query()->where('user_id', $user->id)->findOrFail($this->favoriteId);

        $reminders->schedule($user, $favorite, CarbonImmutable::parse($this->remindAt), $this->reminderText ?: null);
        $this->dispatch('favorite-collections-changed');
    }

    public function cancel(FavoriteReminderService $reminders): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $favorite = Favorite::query()->where('user_id', $user->id)->findOrFail($this->favoriteId);

        $reminders->cancel($user, $favorite);
        $this->dispatch('favorite-collections-changed');
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-reminder-sheet');
    }
}
