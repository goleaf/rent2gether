<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('compatibility.filter.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.filter.helper') }}</flux:text>
    </div>

    <flux:select wire:model.change="minimumFit" label="{{ __('compatibility.filter.minimum_fit') }}">
        @foreach(['great', 'good', 'attention', 'uncomfortable'] as $status)
            <flux:select.option value="{{ $status }}">{{ __('compatibility.fit_statuses.'.$status) }}</flux:select.option>
        @endforeach
    </flux:select>

    <div class="grid gap-3">
        <flux:checkbox wire:model.change="hideNotSuitable" label="{{ __('compatibility.filter.hide_not_suitable') }}" />
        <flux:checkbox wire:model.change="showWarnings" label="{{ __('compatibility.filter.show_warnings') }}" />
    </div>

    <flux:button type="button" variant="primary" class="w-full" icon="funnel" wire:click="apply">
        {{ __('compatibility.actions.apply_filter') }}
    </flux:button>
</flux:card>
