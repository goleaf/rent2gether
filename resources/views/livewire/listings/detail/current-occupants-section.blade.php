<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ $summary['confirmed'] ? __('occupants.confirmed_title') : __('occupants.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('occupants.helper') }}</flux:text>
    </div>

    <div class="space-y-2">
        @forelse($summary['messages'] as $message)
            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $message }}</p>
        @empty
            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ __('occupants.no_occupants') }}</p>
        @endforelse
    </div>

    @if($summary['badges'] !== [])
        <div class="flex flex-wrap gap-2">
            @foreach(array_slice($summary['badges'], 0, 8) as $badge)
                <flux:badge color="zinc" icon="home-modern">{{ $badge }}</flux:badge>
            @endforeach
        </div>
    @endif

    @if($summary['warnings'] !== [])
        <div class="space-y-2">
            @foreach($summary['warnings'] as $warning)
                <flux:badge color="amber" icon="exclamation-triangle">{{ $warning['message'] }}</flux:badge>
            @endforeach
        </div>
    @endif

    @if($summary['cards'] !== [])
        <div class="space-y-3">
            @foreach($summary['cards'] as $card)
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            @if($card['alias'])
                                <p class="font-medium text-zinc-900 dark:text-zinc-50">{{ $card['alias'] }}</p>
                            @endif
                            @if($card['age_range'])
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $card['age_range'] }}</p>
                            @endif
                        </div>
                        @if($card['roommate_rating'])
                            <flux:badge color="green" icon="check-circle">{{ __('occupants.roommate_rating') }}: {{ number_format($card['roommate_rating'], 1) }}</flux:badge>
                        @endif
                    </div>

                    @if($card['badges'] !== [])
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($card['badges'] as $badge)
                                <flux:badge color="zinc" icon="home-modern">{{ $badge }}</flux:badge>
                            @endforeach
                        </div>
                    @endif

                    @if($card['languages'] !== [] || $card['checkout_date_label'])
                        <div class="mt-2 space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
                            @if($card['languages'] !== [])
                                <p>{{ __('occupants.languages', ['languages' => implode(', ', $card['languages'])]) }}</p>
                            @endif
                            @if($card['checkout_date_label'])
                                <p>{{ $card['checkout_date_label'] }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $summary['privacy_note'] }}</flux:text>
</flux:card>
