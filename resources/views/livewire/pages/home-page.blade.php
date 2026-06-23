<div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-900 p-6 text-white shadow-xl shadow-zinc-950/20">
            <div class="space-y-3">
                <flux:badge color="emerald" icon="check-circle">{{ __('app.marketplace_label') }}</flux:badge>
                <flux:heading size="xl" level="1">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('app.home_heading') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="max-w-xl text-white/80">{{ __('app.home_subtitle') }}</flux:text>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <div class="text-sm text-white/70">{{ __('app.home_cards.sleeping_places') }}</div>
                    <div class="mt-2 text-2xl font-semibold">1</div>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <div class="text-sm text-white/70">{{ __('app.home_cards.calculations') }}</div>
                    <div class="mt-2 text-2xl font-semibold">24/7</div>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <div class="text-sm text-white/70">{{ __('app.home_cards.languages') }}</div>
                    <div class="mt-2 text-2xl font-semibold">2+</div>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <div class="text-sm text-white/70">{{ __('app.home_cards.speed') }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ __('app.home_cards.mobile_fast') }}</div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <flux:card class="space-y-3">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('app.home_guest_title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.home_guest_text') }}</flux:text>
                <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" variant="primary" wire:navigate icon="magnifying-glass">
                    {{ __('app.search_places') }}
                </flux:button>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('app.home_host_title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.home_host_text') }}</flux:text>
                <flux:button href="{{ route('health', ['locale' => app()->getLocale()]) }}" wire:navigate icon="eye">
                    {{ __('app.view_health') }}
                </flux:button>
            </flux:card>
        </section>

        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('app.home_calculation_heading') }}</flux:callout.heading>
            <flux:callout.text>{{ __('app.home_calculation_text') }}</flux:callout.text>
        </flux:callout>

        <flux:card class="space-y-3">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <flux:icon name="magnifying-glass" class="size-5" />
                </div>
                <div class="space-y-1">
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.home_empty_title') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.home_empty_text') }}</flux:text>
                </div>
            </div>
            <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                {{ __('app.home_empty_note') }}
            </div>
        </flux:card>
    </div>
