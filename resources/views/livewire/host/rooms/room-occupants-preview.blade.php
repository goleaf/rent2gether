<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('occupants.host_preview.title') }}</flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('occupants.host_preview.helper') }}</flux:text>
    </div>

    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-50">
        {{ __('occupants.occupants_count', ['count' => $summary['occupants_count']]) }}
    </p>

    @if($summary['badges'] !== [])
        <div class="flex flex-wrap gap-2">
            @foreach(array_slice($summary['badges'], 0, 8) as $badge)
                <flux:badge color="zinc">{{ $badge }}</flux:badge>
            @endforeach
        </div>
    @endif

    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('occupants.host_preview.privacy') }}</flux:text>
</flux:card>
