<div>
    @if($hints)
        <flux:card class="space-y-4" data-booking-section="guest-hints">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('guest_hints.before_booking') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_hints.before_booking_helper') }}</flux:text>
        </div>

        <div class="space-y-2">
            @forelse($hints as $hint)
                <div class="rounded-lg border px-3 py-2 text-sm {{ $hint['critical_before_booking'] ? 'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100' : 'border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-medium">{{ $hint['text'] }}</div>
                            <div class="mt-1 text-xs opacity-80">{{ __('guest_hints.categories.'.$hint['category']) }}</div>
                        </div>

                        @if($hint['dismissible'])
                            <livewire:hints.dismiss-hint-button
                                :hint-key="$hint['key']"
                                :sleeping-place-id="$sleepingPlaceId"
                                context="before_booking"
                                :critical="$hint['critical_before_booking']"
                                :key="'hint-dismiss-booking-'.$hint['key'].'-'.$sleepingPlaceId"
                            />
                        @endif
                    </div>
                </div>
            @empty
            @endforelse
        </div>

        <flux:button type="button" variant="primary" class="w-full" wire:click="confirm" icon="arrow-right">
            {{ __('guest_hints.actions.understood_continue') }}
        </flux:button>
        </flux:card>
    @endif
</div>
