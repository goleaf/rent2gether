<flux:card class="space-y-3">
    <div class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:heading size="sm" class="truncate">
                    <a href="{{ $card['href'] }}" wire:navigate class="hover:text-emerald-700 dark:hover:text-emerald-300">
                        {{ $card['title'] }}
                    </a>
                </flux:heading>
                <flux:text size="sm" class="truncate text-zinc-600 dark:text-zinc-400">{{ $card['location'] ?: __('saved_searches.no_location') }}</flux:text>
            </div>
            <flux:badge size="sm" color="{{ $card['status'] === 'active' ? 'green' : ($card['status'] === 'paused' ? 'amber' : 'zinc') }}">
                {{ $card['status_label'] }}
            </flux:badge>
        </div>

        <div class="grid grid-cols-2 gap-2 text-sm text-zinc-600 dark:text-zinc-300">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-950">
                <div class="text-xs text-zinc-500">{{ __('saved_searches.dates') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $card['dates'] }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-950">
                <div class="text-xs text-zinc-500">{{ __('saved_searches.budget') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $card['budget'] }}</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-1.5">
            <flux:badge size="sm">{{ trans_choice('saved_searches.counts.new_matches', $card['new_matches_count'], ['count' => $card['new_matches_count']]) }}</flux:badge>
            <flux:badge size="sm" color="green">{{ trans_choice('saved_searches.counts.price_drops', $card['price_drops_count'], ['count' => $card['price_drops_count']]) }}</flux:badge>
            <flux:badge size="sm" color="blue">{{ trans_choice('saved_searches.counts.available_again', $card['available_again_count'], ['count' => $card['available_again_count']]) }}</flux:badge>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-zinc-100 pt-3 text-xs text-zinc-500 dark:border-zinc-800">
            <span>{{ __('saved_searches.last_checked_label', ['time' => $card['last_checked']]) }}</span>
            <span>{{ $card['frequency'] }}</span>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <flux:button href="{{ $card['href'] }}" size="sm" variant="primary" wire:navigate>
                {{ __('saved_searches.open') }}
            </flux:button>
            <flux:button type="button" size="sm" variant="ghost" wire:click="runNow">
                {{ __('saved_searches.run_now') }}
            </flux:button>
            @if($card['status'] === 'active')
                <flux:button type="button" size="sm" variant="ghost" wire:click="pause">
                    {{ __('saved_searches.pause') }}
                </flux:button>
            @else
                <flux:button type="button" size="sm" variant="ghost" wire:click="resume">
                    {{ __('saved_searches.resume') }}
                </flux:button>
            @endif
            <flux:button type="button" size="sm" variant="danger" wire:click="archive">
                {{ __('saved_searches.archive') }}
            </flux:button>
        </div>
    </div>
</flux:card>
