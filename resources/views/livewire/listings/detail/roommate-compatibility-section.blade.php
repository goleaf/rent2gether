<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="scale" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('occupants.compatibility.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('occupants.compatibility.helper') }}</flux:text>
    </div>

    @if($compatibility['warnings'] !== [])
        <div class="space-y-2">
            @foreach($compatibility['warnings'] as $warning)
                <flux:badge color="amber" icon="exclamation-triangle">{{ $warning['message'] }}</flux:badge>
            @endforeach
        </div>
    @else
        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('occupants.compatibility.no_warnings') }}</p>
    @endif
</flux:card>
