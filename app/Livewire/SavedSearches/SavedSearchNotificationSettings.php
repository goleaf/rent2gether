<?php

namespace App\Livewire\SavedSearches;

use App\Models\SavedSearch;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SavedSearchNotificationSettings extends Component
{
    #[Locked]
    public int $savedSearchId;

    public bool $notifyNewMatches = true;

    public bool $notifyPriceDrops = true;

    public bool $notifyAvailableAgain = true;

    public bool $notifyBetterMatch = true;

    public string $notificationFrequency = 'on_visit';

    public bool $quietHoursEnabled = true;

    public string $quietHoursStart = '22:00';

    public string $quietHoursEnd = '07:00';

    public function mount(int $savedSearchId): void
    {
        $this->savedSearchId = $savedSearchId;
        $search = SavedSearch::query()->findOrFail($savedSearchId);
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $search->user_id === (int) $user->id, 403);

        $this->notifyNewMatches = (bool) $search->notify_new_matches;
        $this->notifyPriceDrops = (bool) $search->notify_price_drops;
        $this->notifyAvailableAgain = (bool) $search->notify_available_again;
        $this->notifyBetterMatch = (bool) $search->notify_better_match;
        $this->notificationFrequency = $search->notification_frequency ?: 'on_visit';
        $this->quietHoursEnabled = (bool) $search->quiet_hours_enabled;
        $this->quietHoursStart = $search->quiet_hours_start ?: '22:00';
        $this->quietHoursEnd = $search->quiet_hours_end ?: '07:00';
    }

    public function save(SavedSearchService $savedSearches): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $this->validate([
            'quietHoursStart' => ['nullable', 'date_format:H:i'],
            'quietHoursEnd' => ['nullable', 'date_format:H:i'],
        ], [], [
            'quietHoursStart' => __('saved_searches.quiet_hours_start'),
            'quietHoursEnd' => __('saved_searches.quiet_hours_end'),
        ]);

        $savedSearches->update($user, SavedSearch::query()->findOrFail($this->savedSearchId), [
            'notify_new_matches' => $this->notifyNewMatches,
            'notify_price_drops' => $this->notifyPriceDrops,
            'notify_available_again' => $this->notifyAvailableAgain,
            'notify_better_match' => $this->notifyBetterMatch,
            'notification_frequency' => $this->notificationFrequency,
            'quiet_hours_enabled' => $this->quietHoursEnabled,
            'quiet_hours_start' => $this->quietHoursStart,
            'quiet_hours_end' => $this->quietHoursEnd,
        ]);

        $this->dispatch('saved-search-updated');
    }

    public function render(): View
    {
        return view('livewire.saved-searches.saved-search-notification-settings');
    }
}
