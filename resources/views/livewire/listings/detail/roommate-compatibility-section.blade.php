<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('occupants.compatibility.title') }}</flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('occupants.compatibility.helper') }}</flux:text>
    </div>

    @if($compatibility['warnings'] !== [])
        <div class="space-y-2">
            @foreach($compatibility['warnings'] as $warning)
                <flux:badge color="amber">{{ $warning['message'] }}</flux:badge>
            @endforeach
        </div>
    @else
        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('occupants.compatibility.no_warnings') }}</p>
    @endif
</flux:card>
