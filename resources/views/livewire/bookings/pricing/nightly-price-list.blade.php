<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('pricing.sections.nightly_lines') }}</flux:heading>

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
            <flux:callout color="zinc">
                <flux:callout.heading>{{ __('pricing.empty.nightly_lines') }}</flux:callout.heading>
            </flux:callout>
        @endforelse
    </div>
</flux:card>
