<div class="fixed inset-0 z-50">
    <flux:button type="button" variant="ghost" class="absolute inset-0 h-auto w-full rounded-none bg-zinc-950/50 p-0 hover:bg-zinc-950/50 dark:hover:bg-zinc-950/50" wire:click="$dispatch('saved-search-updated')" aria-label="{{ __('saved_searches.close') }}" />

    <section class="absolute inset-x-0 bottom-0 max-h-[88vh] overflow-y-auto rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:top-4 sm:w-full sm:max-w-md sm:rounded-xl">
        <form wire:submit="save" class="space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('saved_searches.create_title') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('saved_searches.create_helper') }}</flux:text>
                </div>
                <flux:button type="button" variant="ghost" size="sm" wire:click="$dispatch('saved-search-updated')" icon="x-mark">
                    {{ __('saved_searches.close') }}
                </flux:button>
            </div>

            @include('livewire.saved-searches.partials.saved-search-form-fields')

            <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="magnifying-glass">
                    <span wire:loading.remove wire:target="save">{{ __('saved_searches.save_search') }}</span>
                    <span wire:loading wire:target="save">{{ __('saved_searches.saving') }}</span>
                </flux:button>
            </div>
        </form>
    </section>
</div>
