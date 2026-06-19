<div class="max-w-4xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('search.favorites.title') }}</flux:heading>

    @if($this->collections->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            <flux:button size="sm" wire:click="$set('selectedCollection', '')" :variant="$selectedCollection === '' ? 'primary' : 'ghost'">
                {{ __('search.favorites.all') }}
            </flux:button>
            @foreach($this->collections as $collection)
                <flux:button size="sm" wire:click="$set('selectedCollection', '{{ $collection }}')" :variant="$selectedCollection === $collection ? 'primary' : 'ghost'">
                    {{ $collection }}
                </flux:button>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($this->favorites as $favorite)
            <flux:card class="space-y-3">
                <div class="h-32 bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center">
                    <flux:icon name="home" class="size-8 text-zinc-300" />
                </div>
                <div>
                    <a href="{{ route('beds.show', ['locale' => app()->getLocale(), 'bed' => $favorite->bed]) }}">
                        <flux:heading size="sm" class="hover:text-blue-600 transition">{{ $favorite->bed->title }}</flux:heading>
                    </a>
                    <flux:text size="sm" class="text-zinc-500">{{ $favorite->bed->room->property->city }}</flux:text>
                    <flux:text class="font-semibold">&euro;{{ number_format($favorite->bed->price_per_night, 2) }}/{{ __('app.units.night') }}</flux:text>
                    @if($favorite->priceChanged())
                        <flux:badge size="sm" color="{{ $favorite->bed->price_per_night < $favorite->price_at_save ? 'green' : 'red' }}">
                            {{ $favorite->bed->price_per_night < $favorite->price_at_save ? __('search.favorites.price_dropped') : __('search.favorites.price_increased') }}
                        </flux:badge>
                    @endif
                </div>
                <flux:button size="sm" variant="ghost" wire:click="remove({{ $favorite->id }})" icon="heart" class="text-red-500">
                    {{ __('app.actions.remove') }}
                </flux:button>
            </flux:card>
        @empty
            <div class="col-span-full">
                <flux:card>
                    <flux:text class="text-center text-zinc-500 py-8">{{ __('search.favorites.empty') }}</flux:text>
                </flux:card>
            </div>
        @endforelse
    </div>
</div>
