<div class="mx-auto flex min-h-screen w-full max-w-xl flex-col gap-5 px-4 pb-24 pt-6">
    @if($this->context)
        <section class="space-y-2">
            <flux:badge color="lime">{{ __('domain.entities.sleeping_place') }}</flux:badge>
            <flux:heading size="xl">{{ $this->context['place']['title'] }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('sleeping_places.public.helper') }}</flux:text>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:heading size="sm">{{ __('sleeping_places.public.price_title') }}</flux:heading>
            <div class="mt-2 text-2xl font-semibold">
                {{ __('sleeping_places.card.price_value', ['amount' => $this->context['place']['price'], 'currency' => $this->context['place']['currency']]) }}
            </div>
        </section>

        <section class="grid gap-3">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm">{{ __('domain.entities.room') }}</flux:heading>
                <flux:text>{{ $this->context['room']['title'] }}</flux:text>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm">{{ __('domain.entities.property') }}</flux:heading>
                <flux:text>{{ $this->context['property']['title'] }}</flux:text>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm">{{ __('domain.entities.host') }}</flux:heading>
                <flux:text>{{ $this->context['host']['name'] }}</flux:text>
            </div>
        </section>
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
            {{ __('sleeping_places.empty.not_found') }}
        </div>
    @endif
</div>
