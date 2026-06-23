<div class="space-y-4">
    <div class="space-y-1">
        <flux:heading>
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('calendar.host.room_title') }}</span>
            </span>
        </flux:heading>
        <flux:text>{{ __('calendar.host.room_helper') }}</flux:text>
    </div>

    <div class="space-y-3">
        @forelse($this->cards as $card)
            <flux:card class="space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="sm">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ $card['date'] }}</span>
                            </span>
                        </flux:heading>
                        <flux:text size="sm">{{ $card['title'] }}</flux:text>
                    </div>
                    <flux:badge icon="home-modern">{{ $card['status_label'] }}</flux:badge>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text>{{ __('calendar.empty.days') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</div>
