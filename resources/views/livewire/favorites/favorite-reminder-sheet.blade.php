<div class="fixed inset-0 z-40">
    <flux:button type="button" variant="ghost" class="absolute inset-0 h-auto w-full rounded-none bg-zinc-950/50 p-0 hover:bg-zinc-950/50 dark:hover:bg-zinc-950/50" wire:click="$dispatch('favorite-collections-changed')" aria-label="{{ __('favorites.close') }}" />

    <section class="absolute inset-x-0 bottom-0 max-h-[86vh] overflow-y-auto rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:top-4 sm:w-full sm:max-w-sm sm:rounded-xl">
        <div class="mb-4">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('favorites.remind_later') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('favorites.reminder_helper') }}</flux:text>
        </div>

        <div class="mb-4 grid grid-cols-3 gap-2">
            <flux:button type="button" size="sm" variant="ghost" wire:click="choose('tomorrow')" icon="heart">{{ __('favorites.reminder_options.tomorrow') }}</flux:button>
            <flux:button type="button" size="sm" variant="ghost" wire:click="choose('three_days')" icon="heart">{{ __('favorites.reminder_options.three_days') }}</flux:button>
            <flux:button type="button" size="sm" variant="ghost" wire:click="choose('week')" icon="heart">{{ __('favorites.reminder_options.week') }}</flux:button>
        </div>

        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('favorites.fields.remind_at') }}</span>
    </span>
</flux:label>
                <flux:input type="datetime-local" wire:model.change="remindAt" icon="calendar-days" />
                <flux:error name="remindAt" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('favorites.fields.reminder_text') }}</span>
    </span>
</flux:label>
                <flux:textarea rows="3" wire:model.blur="reminderText" />
                <flux:error name="reminderText" />
            </flux:field>

            <div class="grid grid-cols-2 gap-2">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" icon="heart">{{ __('favorites.schedule_reminder') }}</flux:button>
                <flux:button type="button" variant="ghost" wire:click="cancel" wire:loading.attr="disabled" icon="x-mark">{{ __('favorites.cancel_reminder') }}</flux:button>
            </div>
        </form>
    </section>
</div>
