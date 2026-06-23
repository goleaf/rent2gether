<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="{{ $offer->status === 'active' ? 'green' : 'zinc' }}" icon="check-circle">{{ __('waitlist.offer_available') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ $title }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('waitlist.messages.offer_created') }}</flux:text>
    </section>

    <flux:card class="space-y-3">
        <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-zinc-500">{{ __('waitlist.desired_dates') }}</div>
                <div class="font-medium">{{ $item?->desired_check_in_date?->format('d.m') }} - {{ $item?->desired_check_out_date?->format('d.m') }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-zinc-500">{{ __('waitlist.nights_count') }}</div>
                <div class="font-medium">{{ $item?->nights_count }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-zinc-500">{{ __('waitlist.current_price') }}</div>
                <div class="font-medium">{{ $offer->current_total_price }} {{ $offer->currency }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-zinc-500">{{ __('waitlist.max_price') }}</div>
                <div class="font-medium">{{ $item?->max_total_price ?: __('waitlist.states.no_limit') }}</div>
            </div>
        </div>

        @if($offer->status === 'active')
            <flux:callout color="green" icon="check-circle">
                <flux:callout.text>{{ __('waitlist.offer_expires', ['time' => $offer->offer_expires_at?->format('H:i')]) }}</flux:callout.text>
            </flux:callout>
        @else
            <flux:callout color="zinc" icon="information-circle">
                <flux:callout.text>{{ __('waitlist.expired') }}</flux:callout.text>
            </flux:callout>
        @endif
    </flux:card>

    <div class="sticky bottom-20 -mx-4 flex gap-2 border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950 sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0">
        <flux:button type="button" variant="primary" class="flex-1" wire:click="accept" wire:loading.attr="disabled" wire:target="accept" icon="calendar-days">
            {{ __('waitlist.book_now') }}
        </flux:button>
        <flux:button type="button" variant="ghost" class="flex-1" wire:click="decline" wire:loading.attr="disabled" wire:target="decline" icon="x-mark">
            {{ __('waitlist.decline_offer') }}
        </flux:button>
    </div>
</x-ui.page>
