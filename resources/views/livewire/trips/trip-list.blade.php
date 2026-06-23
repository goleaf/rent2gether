<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ __('booking.trips.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.trips.scopes.'.$scope.'.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.trips.scopes.'.$scope.'.helper') }}
            </flux:text>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <flux:button href="{{ route('trips.index', ['locale' => app()->getLocale()]) }}" wire:navigate size="sm" variant="{{ $scope === 'upcoming' ? 'primary' : 'ghost' }}" class="justify-center" icon="calendar-days">
            {{ __('booking.trips.tabs.upcoming') }}
        </flux:button>
        <flux:button href="{{ route('trips.current', ['locale' => app()->getLocale()]) }}" wire:navigate size="sm" variant="ghost" class="justify-center" icon="calendar-days">
            {{ __('booking.trips.tabs.current') }}
        </flux:button>
        <flux:button href="{{ route('trips.scope', ['locale' => app()->getLocale(), 'scope' => 'past']) }}" wire:navigate size="sm" variant="{{ $scope === 'past' ? 'primary' : 'ghost' }}" class="justify-center" icon="calendar-days">
            {{ __('booking.trips.tabs.past') }}
        </flux:button>
        <flux:button href="{{ route('trips.scope', ['locale' => app()->getLocale(), 'scope' => 'cancelled']) }}" wire:navigate size="sm" variant="{{ $scope === 'cancelled' ? 'primary' : 'ghost' }}" class="justify-center" icon="x-mark">
            {{ __('booking.trips.tabs.cancelled') }}
        </flux:button>
    </div>

    <div class="space-y-3">
        @forelse($cards as $card)
            <flux:card class="space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <flux:heading size="lg" class="truncate">{{ $card['title'] }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ $card['city'] }} · {{ $card['dates'] }}
                        </flux:text>
                    </div>

                    <flux:badge color="zinc" icon="calendar-days">{{ $card['status'] }}</flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.property') }}</div>
                        <div class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $card['property'] }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.nights') }}</div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $card['nights'] }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.total') }}</div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $card['total'] }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.reference') }}</div>
                        <div class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $card['booking']->reference }}</div>
                    </div>
                </div>

                <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $card['booking']]) }}" wire:navigate variant="primary" class="w-full" icon="eye">
                    {{ __('booking.trips.actions.open_detail') }}
                </flux:button>
            </flux:card>
        @empty
            <flux:card class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <flux:icon name="calendar-days" class="size-5" />
                    </div>
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('booking.trips.scopes.'.$scope.'.empty_title') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                            {{ __('booking.trips.scopes.'.$scope.'.empty_text') }}
                        </flux:text>
                    </div>
                </div>

                <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" wire:navigate variant="primary" class="w-full" icon="magnifying-glass">
                    {{ __('booking.trips.actions.search_places') }}
                </flux:button>
            </flux:card>
        @endforelse
    </div>

    @if($bookings->hasPages())
        <div>{{ $bookings->links() }}</div>
    @endif
</x-ui.page>
