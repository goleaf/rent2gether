<div class="fixed inset-0 z-40">
    <flux:button type="button" variant="ghost" class="absolute inset-0 h-auto w-full rounded-none bg-zinc-950/50 p-0 hover:bg-zinc-950/50 dark:hover:bg-zinc-950/50" wire:click="$dispatch('favorite-collections-changed')" aria-label="{{ __('favorites.close') }}" />

    <section class="absolute inset-x-0 bottom-0 rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:w-full sm:max-w-sm sm:rounded-xl">
        <div class="mb-4">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('favorites.move_to_collection') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('favorites.move_helper') }}</flux:text>
        </div>

        <form wire:submit="move" class="space-y-4">
            <flux:field>
                <flux:label>{{ __('favorites.collections') }}</flux:label>
                <flux:select wire:model.change="collectionId">
                    <flux:select.option value="">{{ __('favorites.choose_collection') }}</flux:select.option>
                    @foreach($collections as $collection)
                        <flux:select.option value="{{ $collection->id }}">{{ $collection->title }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" icon="heart">
                {{ __('favorites.move_to_collection') }}
            </flux:button>
        </form>
    </section>
</div>
