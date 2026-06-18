<div class="max-w-4xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Saved Searches') }}</flux:heading>

    <div class="space-y-4">
        @forelse($this->searches as $search)
            <flux:card class="flex items-center justify-between">
                <div class="space-y-1">
                    <flux:text class="font-medium">{{ $search->name }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500">
                        @if($search->city){{ $search->city }}@endif
                        @if($search->check_in) &middot; {{ $search->check_in }} - {{ $search->check_out }}@endif
                        @if($search->min_price || $search->max_price) &middot; &euro;{{ $search->min_price ?? 0 }}-{{ $search->max_price ?? '∞' }}@endif
                    </flux:text>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button size="sm" wire:click="runSearch({{ $search->id }})" icon="magnifying-glass">{{ __('Search') }}</flux:button>
                    <flux:button size="sm" wire:click="toggleNotifications({{ $search->id }})" variant="{{ $search->notify_email ? 'primary' : 'ghost' }}" icon="bell">
                        {{ $search->notify_email ? __('On') : __('Off') }}
                    </flux:button>
                    <flux:button size="sm" wire:click="delete({{ $search->id }})" variant="ghost" icon="trash" wire:confirm="{{ __('Delete this saved search?') }}" />
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-8">{{ __('No saved searches yet. Save a search from the search page.') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</div>
