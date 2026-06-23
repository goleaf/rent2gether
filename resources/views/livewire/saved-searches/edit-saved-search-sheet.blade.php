<div class="fixed inset-0 z-50">
    <flux:button type="button" variant="ghost" class="absolute inset-0 h-auto w-full rounded-none bg-zinc-950/50 p-0 hover:bg-zinc-950/50 dark:hover:bg-zinc-950/50" wire:click="$dispatch('saved-search-updated')" aria-label="{{ __('saved_searches.close') }}" />

    <section class="absolute inset-x-0 bottom-0 max-h-[88vh] overflow-y-auto rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:top-4 sm:w-full sm:max-w-md sm:rounded-xl">
        <div class="space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('saved_searches.edit_title') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('saved_searches.edit_helper') }}</flux:text>
                </div>
                <flux:button type="button" variant="ghost" size="sm" wire:click="$dispatch('saved-search-updated')" icon="x-mark">
                    {{ __('saved_searches.close') }}
                </flux:button>
            </div>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="heart" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('saved_searches.search_name') }}</span>
    </span>
</flux:label>
                <flux:input wire:model.blur="title" icon="tag" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="heart" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('saved_searches.description') }}</span>
    </span>
</flux:label>
                <flux:textarea rows="3" wire:model.blur="description" />
                <flux:error name="description" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('saved_searches.budget') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="budgetMax" icon="banknotes" />
                <flux:error name="budgetMax" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('saved_searches.notification_frequency') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="notificationFrequency">
                    @foreach(['on_visit', 'instant', 'daily', 'weekly', 'important_only'] as $frequency)
                        <flux:select.option value="{{ $frequency }}">{{ __('saved_searches.frequency.'.$frequency) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="save" icon="magnifying-glass">
                    <span wire:loading.remove wire:target="save">{{ __('saved_searches.save_changes') }}</span>
                    <span wire:loading wire:target="save">{{ __('saved_searches.saving') }}</span>
                </flux:button>
            </div>
        </div>
    </section>
</div>
