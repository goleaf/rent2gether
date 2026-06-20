<div class="fixed inset-0 z-40">
    <flux:button type="button" variant="ghost" class="absolute inset-0 h-auto w-full rounded-none bg-zinc-950/50 p-0 hover:bg-zinc-950/50 dark:hover:bg-zinc-950/50" wire:click="$dispatch('favorite-collections-changed')" aria-label="{{ __('favorites.close') }}" />

    <section class="absolute inset-x-0 bottom-0 max-h-[86vh] overflow-y-auto rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:top-4 sm:w-full sm:max-w-sm sm:rounded-xl">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <flux:heading size="lg">{{ __('favorites.create_collection') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('favorites.create_collection_helper') }}</flux:text>
            </div>
            <flux:button type="button" variant="ghost" size="sm" wire:click="$dispatch('favorite-collections-changed')">
                {{ __('favorites.close') }}
            </flux:button>
        </div>

        <form wire:submit="create" class="space-y-4">
            <flux:field>
                <flux:label>{{ __('favorites.fields.title') }}</flux:label>
                <flux:input wire:model.blur="title" maxlength="120" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('favorites.fields.description') }}</flux:label>
                <flux:textarea rows="3" wire:model.blur="description" />
                <flux:error name="description" />
            </flux:field>

            <div class="grid grid-cols-2 gap-3">
                <flux:field>
                    <flux:label>{{ __('favorites.fields.icon') }}</flux:label>
                    <flux:select wire:model.change="icon">
                        @foreach(['heart', 'tag', 'briefcase', 'academic-cap', 'calendar-days', 'star', 'bookmark'] as $iconOption)
                            <flux:select.option value="{{ $iconOption }}">{{ __('favorites.icons.'.$iconOption) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('favorites.fields.color') }}</flux:label>
                    <flux:select wire:model.change="color">
                        @foreach(['emerald', 'blue', 'violet', 'amber', 'zinc'] as $colorOption)
                            <flux:select.option value="{{ $colorOption }}">{{ __('favorites.colors.'.$colorOption) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="create">
                    <span wire:loading.remove wire:target="create">{{ __('favorites.create_collection') }}</span>
                    <span wire:loading wire:target="create">{{ __('favorites.saving') }}</span>
                </flux:button>
            </div>
        </form>
    </section>
</div>
