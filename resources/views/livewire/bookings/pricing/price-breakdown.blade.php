<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('pricing.sections.breakdown') }}</flux:heading>

    <div class="space-y-2">
        @foreach ($rows as $row)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <flux:text size="sm">{{ $row['label'] }}</flux:text>
                <flux:text size="sm" class="font-medium">{{ $row['amount'] }}</flux:text>
            </div>
        @endforeach
    </div>
</flux:card>
