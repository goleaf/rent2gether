<x-ui.page class="space-y-0 flex min-h-screen flex-col gap-5">
    @if($this->context)
        <section class="space-y-2">
            <flux:badge color="lime" icon="home-modern">{{ __('domain.entities.sleeping_place') }}</flux:badge>
            <flux:heading size="xl">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $this->context['place']['title'] }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('sleeping_places.public.helper') }}</flux:text>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('sleeping_places.public.price_title') }}</span>
                </span>
            </flux:heading>
            <div class="mt-2 text-2xl font-semibold">
                {{ __('sleeping_places.card.price_value', ['amount' => $this->context['place']['price'], 'currency' => $this->context['place']['currency']]) }}
            </div>
        </section>

        <section class="grid gap-3">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('domain.entities.room') }}</span>
                    </span>
                </flux:heading>
                <flux:text>{{ $this->context['room']['title'] }}</flux:text>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('domain.entities.property') }}</span>
                    </span>
                </flux:heading>
                <flux:text>{{ $this->context['property']['title'] }}</flux:text>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('domain.entities.host') }}</span>
                    </span>
                </flux:heading>
                <flux:text>{{ $this->context['host']['name'] }}</flux:text>
            </div>
        </section>
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
            {{ __('sleeping_places.empty.not_found') }}
        </div>
    @endif
</x-ui.page>
