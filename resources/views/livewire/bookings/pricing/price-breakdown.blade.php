<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('pricing.sections.breakdown') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        @foreach ($rows as $row)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ $row['label'] }}</flux:text>
                <flux:text size="sm" class="font-medium">{{ $row['amount'] }}</flux:text>
            </div>
        @endforeach
    </div>
</flux:card>
