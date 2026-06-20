<div class="space-y-5">
    <section class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:heading size="xl" level="1">{{ $this->search->displayTitle() }}</flux:heading>
                @if($this->search->description)
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $this->search->description }}</flux:text>
                @endif
            </div>
            <flux:badge color="{{ $this->search->status === 'active' ? 'green' : ($this->search->status === 'paused' ? 'amber' : 'zinc') }}">
                {{ __('saved_searches.statuses.'.($this->search->status ?: 'active')) }}
            </flux:badge>
        </div>

        <div class="grid gap-2 sm:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">{{ __('saved_searches.city') }}</div>
                <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $this->search->cityModel?->name ?: $this->search->city ?: __('saved_searches.no_location') }}</div>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">{{ __('saved_searches.dates') }}</div>
                <div class="font-medium text-zinc-950 dark:text-zinc-50">
                    @if($this->search->check_in_date && $this->search->check_out_date)
                        {{ $this->search->check_in_date->toFormattedDateString() }} - {{ $this->search->check_out_date->toFormattedDateString() }}
                    @else
                        {{ __('saved_searches.no_dates') }}
                    @endif
                </div>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">{{ __('saved_searches.nights_count') }}</div>
                <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $this->search->nights_count ?: __('saved_searches.no_dates') }}</div>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">{{ __('saved_searches.budget') }}</div>
                <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $this->search->budget_max ? \Illuminate\Support\Number::currency((float) $this->search->budget_max, $this->search->currency ?: 'EUR', app()->getLocale()) : __('saved_searches.no_budget') }}</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($this->search->require_wifi)
                <flux:badge size="sm">{{ __('saved_searches.filters.wifi') }}</flux:badge>
            @endif
            @if($this->search->require_locker)
                <flux:badge size="sm">{{ __('saved_searches.filters.locker') }}</flux:badge>
            @endif
            @if($this->search->only_instant_booking)
                <flux:badge size="sm" color="green">{{ __('saved_searches.instant_booking_only') }}</flux:badge>
            @endif
            @if($this->search->only_verified_hosts)
                <flux:badge size="sm" color="blue">{{ __('saved_searches.verified_hosts_only') }}</flux:badge>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
            <flux:button type="button" variant="primary" wire:click="runNow" class="data-loading:opacity-70">
                {{ __('saved_searches.run_now') }}
            </flux:button>
            <flux:button type="button" variant="ghost" wire:click="$set('editOpen', true)">
                {{ __('saved_searches.edit') }}
            </flux:button>
            <flux:button type="button" variant="ghost" wire:click="$set('settingsOpen', true)">
                {{ __('saved_searches.notification_settings') }}
            </flux:button>
            @if($this->search->status === 'active')
                <flux:button type="button" variant="ghost" wire:click="pause">{{ __('saved_searches.pause') }}</flux:button>
            @else
                <flux:button type="button" variant="ghost" wire:click="resume">{{ __('saved_searches.resume') }}</flux:button>
            @endif
            <flux:button type="button" variant="danger" wire:click="archive">{{ __('saved_searches.archive') }}</flux:button>
        </div>
    </section>

    @if($editOpen)
        <livewire:saved-searches.edit-saved-search-sheet :saved-search-id="$savedSearchId" :key="'edit-saved-search-'.$savedSearchId" />
    @endif

    @if($settingsOpen)
        <livewire:saved-searches.saved-search-notification-settings :saved-search-id="$savedSearchId" :key="'saved-search-settings-'.$savedSearchId" />
    @endif

    @foreach([
        'new' => $this->search->new_matches_count,
        'price_drops' => $this->search->price_drops_count,
        'available_again' => $this->search->available_again_count,
        'all' => 1,
    ] as $section => $count)
        @if($count > 0)
            <section class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="lg">{{ __('saved_searches.sections.'.$section) }}</flux:heading>
                    @if($section === 'all')
                        <flux:text size="sm" class="text-zinc-500">{{ __('saved_searches.last_checked_label', ['time' => $this->search->last_checked_at?->diffForHumans() ?: __('saved_searches.never_checked')]) }}</flux:text>
                    @endif
                </div>
                <livewire:saved-searches.saved-search-results-list :saved-search-id="$savedSearchId" :section="$section" :key="'saved-search-results-'.$savedSearchId.'-'.$section" />
            </section>
        @endif
    @endforeach
</div>
