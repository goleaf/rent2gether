<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('pricing.sections.fees') }}</flux:heading>

    <div class="space-y-2">
        @forelse ($lines as $line)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ $line['label'] }}</flux:text>
                <flux:text size="sm" class="font-medium">{{ $line['amount'] }}</flux:text>
            </div>
        @empty
            <flux:callout color="zinc">
                <flux:callout.heading>{{ __('pricing.empty.fees') }}</flux:callout.heading>
            </flux:callout>
        @endforelse
    </div>
</flux:card>
