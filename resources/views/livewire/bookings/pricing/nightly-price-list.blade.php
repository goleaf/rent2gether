<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('pricing.sections.nightly_lines') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        @forelse ($lines as $line)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div>
                    <flux:text size="sm">{{ $line['label'] }}</flux:text>
                    <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ $line['date'] }}</flux:text>
                </div>
                <flux:text size="sm" class="font-medium">{{ $line['amount'] }}</flux:text>
            </div>
        @empty
            <flux:callout color="zinc" icon="information-circle">
                <flux:callout.heading>{{ __('pricing.empty.nightly_lines') }}</flux:callout.heading>
            </flux:callout>
        @endforelse
    </div>
</flux:card>
