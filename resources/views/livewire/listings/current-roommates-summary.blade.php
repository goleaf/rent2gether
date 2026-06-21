<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="md">{{ __('occupants.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('occupants.messages.roommates_summary_private') }}</flux:text>
    </div>

    @forelse ($roommates as $roommate)
        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
            <flux:text class="font-medium">{{ $roommate['label'] ?? __('occupants.messages.roommate') }}</flux:text>
            @if (! empty($roommate['stay_purpose']))
                <flux:text size="sm">{{ __('occupants.purposes.'.$roommate['stay_purpose']) }}</flux:text>
            @endif
        </div>
    @empty
        <flux:text size="sm">{{ __('occupants.messages.no_current_roommates') }}</flux:text>
    @endforelse
</flux:card>
