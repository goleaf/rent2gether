<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ __('host.listings.home.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('shell.pages.host.home.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                {{ __('shell.pages.host.home.helper') }}
            </flux:text>
        </div>

        <div class="grid gap-2 sm:grid-cols-3">
            <flux:button href="{{ route('host.listings.create', ['locale' => app()->getLocale()]) }}" variant="primary" icon="plus" wire:navigate>
                {{ __('listing_wizard.title') }}
            </flux:button>
            <flux:button href="{{ route('host.properties.index', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate icon="home-modern">
                {{ __('host.listings.actions.my_properties') }}
            </flux:button>
            <flux:button href="{{ route('host.listings.index', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate icon="home-modern">
                {{ __('host.listings.actions.all_listings') }}
            </flux:button>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <flux:card class="space-y-1 text-center">
            <div class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $metrics['properties'] }}</div>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.properties') }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 text-center">
            <div class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $metrics['free_places'] }}</div>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.free_beds') }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 text-center">
            <div class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $metrics['occupied_places'] }}</div>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.metrics.occupied_places') }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 text-center">
            <div class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $metrics['pending_requests'] }}</div>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.pending_requests') }}</flux:text>
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.listings.income.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.income.helper') }}</flux:text>
            </div>
            <div class="text-right">
                <div class="text-xl font-semibold">{{ $this->money($metrics['monthly_income'], $metrics['currency']) }}</div>
                <flux:badge color="zinc" icon="banknotes">{{ __('host.listings.income.placeholder') }}</flux:badge>
            </div>
        </div>
        <flux:button size="sm" href="{{ route('host.income', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate icon="banknotes">
            {{ __('host.listings.income.action') }}
        </flux:button>
    </flux:card>

    <div class="grid gap-4 lg:grid-cols-2">
        <flux:card class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.upcoming_checkins') }}</span>
                    </span>
                </flux:heading>
                <flux:badge color="sky" icon="clock">{{ $metrics['upcoming_checkins'] }}</flux:badge>
            </div>
            @forelse($dashboard['upcoming_checkins'] as $booking)
                <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $booking['place'] }}</div>
                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.booking_line', ['guest' => $booking['guest'], 'date' => $booking['date']]) }}</div>
                </div>
            @empty
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.empty_checkins') }}</flux:text>
            @endforelse
        </flux:card>

        <flux:card class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.listings.metrics.upcoming_checkouts') }}</span>
                    </span>
                </flux:heading>
                <flux:badge color="amber" icon="exclamation-triangle">{{ $metrics['upcoming_checkouts'] }}</flux:badge>
            </div>
            @forelse($dashboard['upcoming_checkouts'] as $booking)
                <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $booking['place'] }}</div>
                    <div class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.booking_line', ['guest' => $booking['guest'], 'date' => $booking['date']]) }}</div>
                </div>
            @empty
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.empty_checkouts') }}</flux:text>
            @endforelse
        </flux:card>
    </div>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host.listings.home.properties_title') }}</span>
                </span>
            </flux:heading>
            <flux:button size="sm" href="{{ route('host.listings.index', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate icon="eye">
                {{ __('host.listings.actions.view_all') }}
            </flux:button>
        </div>

        @forelse($dashboard['properties'] as $property)
            @include('livewire.shell.partials.host-property-card', ['property' => $property])
        @empty
            <flux:card class="space-y-3 text-center">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('shell.pages.host.home.empty_title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('shell.pages.host.home.empty_text') }}</flux:text>
                <flux:button href="{{ route('host.listings.create', ['locale' => app()->getLocale()]) }}" variant="primary" wire:navigate icon="plus">
                    {{ __('listing_wizard.title') }}
                </flux:button>
            </flux:card>
        @endforelse
    </section>

    @if($dashboard['tips'])
        <flux:card class="space-y-3">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host.listings.tips_title') }}</span>
                </span>
            </flux:heading>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($dashboard['tips'] as $tip)
                    <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                        {{ __($tip['label_key'], $tip['params'] ?? []) }}
                    </div>
                @endforeach
            </div>
        </flux:card>
    @endif
</x-ui.page>
