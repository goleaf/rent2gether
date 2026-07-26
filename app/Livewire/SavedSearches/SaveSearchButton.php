<?php

namespace App\Livewire\SavedSearches;

use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use App\Support\SavedSearches\SavedSearchFormOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SaveSearchButton extends Component
{
    public ?int $cityId = null;

    public string $cityName = '';

    public string $district = '';

    public string $checkIn = '';

    public string $checkOut = '';

    public int $guestsCount = 1;

    public string $priceMin = '';

    public string $priceMax = '';

    public string $currency = 'EUR';

    public string $roomType = '';

    public string $sleepingPlaceType = '';

    public bool $instantBooking = false;

    public bool $verifiedHost = false;

    public bool $hasReviews = false;

    public bool $requireWifi = false;

    public bool $requireKitchen = false;

    public bool $requireWashingMachine = false;

    public bool $requireLocker = false;

    public bool $requireWorkspace = false;

    public bool $openSheet = false;

    public string $title = '';

    public bool $notifyNewMatches = true;

    public bool $notifyPriceDrops = true;

    public bool $notifyAvailableAgain = true;

    public string $notificationFrequency = 'on_visit';

    public bool $saved = false;

    public function mount(): void
    {
        $this->title = $this->suggestedTitle();
    }

    public function open(): void
    {
        $this->openSheet = true;
        $this->saved = false;
    }

    public function save(SavedSearchService $savedSearches): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            session()->put('intended_saved_search', $this->payload());
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        $this->validate([
            'title' => ['required', 'string', 'max:160'],
            'notifyNewMatches' => ['boolean'],
            'notifyPriceDrops' => ['boolean'],
            'notifyAvailableAgain' => ['boolean'],
            'notificationFrequency' => ['required', Rule::in(SavedSearchFormOptions::notificationFrequencies())],
        ], [], [
            'title' => __('saved_searches.search_name'),
            'notifyNewMatches' => __('saved_searches.notify_new_matches'),
            'notifyPriceDrops' => __('saved_searches.notify_price_drops'),
            'notifyAvailableAgain' => __('saved_searches.notify_available_again'),
            'notificationFrequency' => __('saved_searches.notification_frequency'),
        ]);

        $savedSearches->create($user, $this->payload());

        $this->saved = true;
        $this->openSheet = false;
        $this->dispatch('saved-search-created');
    }

    public function render(): View
    {
        return view('livewire.saved-searches.save-search-button');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'title' => $this->title,
            'city_id' => $this->cityId,
            'city_name' => $this->cityName ?: null,
            'district' => $this->district ?: null,
            'check_in_date' => $this->checkIn ?: null,
            'check_out_date' => $this->checkOut ?: null,
            'guests_count' => max(1, $this->guestsCount),
            'budget_min' => $this->priceMin !== '' ? (float) $this->priceMin : null,
            'budget_max' => $this->priceMax !== '' ? (float) $this->priceMax : null,
            'currency' => $this->currency ?: 'EUR',
            'room_type' => $this->roomType ?: null,
            'sleeping_place_type' => $this->sleepingPlaceType ?: null,
            'only_instant_booking' => $this->instantBooking,
            'only_verified_hosts' => $this->verifiedHost,
            'only_with_reviews' => $this->hasReviews,
            'require_wifi' => $this->requireWifi,
            'require_kitchen' => $this->requireKitchen,
            'require_washing_machine' => $this->requireWashingMachine,
            'require_locker' => $this->requireLocker,
            'require_workspace' => $this->requireWorkspace,
            'notify_new_matches' => $this->notifyNewMatches,
            'notify_price_drops' => $this->notifyPriceDrops,
            'notify_available_again' => $this->notifyAvailableAgain,
            'notification_frequency' => $this->notificationFrequency,
        ];
    }

    private function suggestedTitle(): string
    {
        $parts = array_filter([
            $this->cityName ?: null,
            $this->priceMax !== '' ? __('saved_searches.suggested_title_budget', ['amount' => $this->priceMax, 'currency' => $this->currency]) : null,
        ]);

        return Str::limit($parts === [] ? __('saved_searches.defaults.title') : implode(' ', $parts), 160, '');
    }
}
