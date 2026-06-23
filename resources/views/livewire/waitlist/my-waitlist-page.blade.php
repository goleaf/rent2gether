<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('waitlist.title') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('waitlist.my_waitlist') }}</span>
            </span>
        </flux:heading>
        <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('waitlist.helper') }}</flux:text>
    </section>

    <div wire:loading.delay class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
        {{ __('waitlist.states.updating') }}
    </div>

    <section class="grid gap-3">
        @forelse($cards as $card)
            <flux:card class="space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <flux:heading size="sm" class="truncate">{{ $card['title'] }}</flux:heading>
                        @if($card['location'] !== '')
                            <flux:text size="sm" class="text-zinc-500">{{ $card['location'] }}</flux:text>
                        @endif
                    </div>
                    <flux:badge size="sm" color="{{ $card['item']->status === 'offered' ? 'green' : 'zinc' }}" icon="exclamation-triangle">{{ __('waitlist.statuses.'.$card['item']->status) }}</flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('waitlist.desired_dates') }}</div>
                        <div class="font-medium">{{ $card['item']->desired_check_in_date?->format('d.m') }} - {{ $card['item']->desired_check_out_date?->format('d.m') }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('waitlist.nights_count') }}</div>
                        <div class="font-medium">{{ $card['item']->nights_count }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('waitlist.position') }}</div>
                        <div class="font-medium">{{ $card['item']->position ?: __('waitlist.states.checking') }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('waitlist.max_price') }}</div>
                        <div class="font-medium">{{ $card['item']->max_price_per_night ? $card['item']->max_price_per_night.' '.$card['item']->currency : __('waitlist.states.no_limit') }}</div>
                    </div>
                </div>

                @if($card['item']->activeOffer)
                    <flux:callout color="green" icon="check-circle">
                        <flux:callout.text>{{ __('waitlist.offer_expires', ['time' => $card['item']->activeOffer->offer_expires_at?->format('H:i')]) }}</flux:callout.text>
                    </flux:callout>
                @endif

                <div class="flex flex-wrap gap-2">
                    @if($card['place'])
                        <flux:button size="sm" variant="ghost" icon="eye" href="{{ route('places.show', ['locale' => app()->getLocale(), 'sleepingPlace' => $card['place']]) }}" wire:navigate>
                            {{ __('waitlist.open_listing') }}
                        </flux:button>
                    @endif
                    <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="cancel({{ $card['item']->id }})" wire:confirm="{{ __('waitlist.confirm_leave') }}">
                        {{ __('waitlist.leave') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <div class="space-y-3 text-center">
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('waitlist.empty.title') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('waitlist.empty.text') }}</flux:text>
                    <flux:button variant="primary" href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" wire:navigate icon="magnifying-glass">
                        {{ __('waitlist.empty.button') }}
                    </flux:button>
                </div>
            </flux:card>
        @endforelse
    </section>
</x-ui.page>
