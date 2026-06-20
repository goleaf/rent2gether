<div class="mx-auto max-w-5xl space-y-5 px-4 py-4 pb-24 sm:px-6 lg:py-6">
    <section class="space-y-2">
        <flux:badge color="emerald">{{ __('waitlist.title') }}</flux:badge>
        <flux:heading size="xl" level="1">{{ __('waitlist.my_waitlist') }}</flux:heading>
        <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('waitlist.helper') }}</flux:text>
    </section>

    <div wire:loading.delay class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
        {{ __('waitlist.states.updating') }}
    </div>

    <section class="grid gap-3">
        @forelse($items as $item)
            @php
                $place = $item->sleepingPlace;
                $title = $place?->translations?->firstWhere('locale', app()->getLocale())?->title
                    ?: $place?->translations?->firstWhere('locale', config('app.fallback_locale', 'en'))?->title
                    ?: $place?->display_name
                    ?: $place?->place_number;
                $location = trim(collect([$place?->property?->district, $place?->property?->city])->filter()->implode(', '));
            @endphp

            <flux:card class="space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <flux:heading size="sm" class="truncate">{{ $title }}</flux:heading>
                        @if($location !== '')
                            <flux:text size="sm" class="text-zinc-500">{{ $location }}</flux:text>
                        @endif
                    </div>
                    <flux:badge size="sm" color="{{ $item->status === 'offered' ? 'green' : 'zinc' }}">{{ __('waitlist.statuses.'.$item->status) }}</flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('waitlist.desired_dates') }}</div>
                        <div class="font-medium">{{ $item->desired_check_in_date?->format('d.m') }} - {{ $item->desired_check_out_date?->format('d.m') }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('waitlist.nights_count') }}</div>
                        <div class="font-medium">{{ $item->nights_count }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('waitlist.position') }}</div>
                        <div class="font-medium">{{ $item->position ?: __('waitlist.states.checking') }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('waitlist.max_price') }}</div>
                        <div class="font-medium">{{ $item->max_price_per_night ? $item->max_price_per_night.' '.$item->currency : __('waitlist.states.no_limit') }}</div>
                    </div>
                </div>

                @if($item->activeOffer)
                    <flux:callout color="green" icon="sparkles">
                        <flux:callout.text>{{ __('waitlist.offer_expires', ['time' => $item->activeOffer->offer_expires_at?->format('H:i')]) }}</flux:callout.text>
                    </flux:callout>
                @endif

                <div class="flex flex-wrap gap-2">
                    @if($place)
                        <flux:button size="sm" variant="ghost" icon="arrow-top-right-on-square" href="{{ route('places.show', ['locale' => app()->getLocale(), 'sleepingPlace' => $place]) }}" wire:navigate>
                            {{ __('waitlist.open_listing') }}
                        </flux:button>
                    @endif
                    <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="cancel({{ $item->id }})" wire:confirm="{{ __('waitlist.confirm_leave') }}">
                        {{ __('waitlist.leave') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <div class="space-y-3 text-center">
                    <flux:heading size="lg">{{ __('waitlist.empty.title') }}</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('waitlist.empty.text') }}</flux:text>
                    <flux:button variant="primary" href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" wire:navigate>
                        {{ __('waitlist.empty.button') }}
                    </flux:button>
                </div>
            </flux:card>
        @endforelse
    </section>
</div>
