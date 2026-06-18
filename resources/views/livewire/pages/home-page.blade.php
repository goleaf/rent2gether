<div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-900 p-6 text-white shadow-xl shadow-zinc-950/20">
            <div class="space-y-3">
                <flux:badge color="emerald">{{ __('app.marketplace_label') }}</flux:badge>
                <flux:heading size="xl" level="1">{{ __('app.home_heading') }}</flux:heading>
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
                <flux:heading size="lg">{{ __('app.home_guest_title') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.home_guest_text') }}</flux:text>
                <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" variant="primary">
                    {{ __('app.search_places') }}
                </flux:button>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="lg">{{ __('app.home_host_title') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.home_host_text') }}</flux:text>
                <flux:button href="{{ route('health', ['locale' => app()->getLocale()]) }}">
                    {{ __('app.view_health') }}
                </flux:button>
            </flux:card>
        </section>

        <flux:callout color="amber" icon="sparkles">
            <flux:callout.heading>{{ __('app.home_calculation_heading') }}</flux:callout.heading>
            <flux:callout.text>{{ __('app.home_calculation_text') }}</flux:callout.text>
        </flux:callout>
    </div>
