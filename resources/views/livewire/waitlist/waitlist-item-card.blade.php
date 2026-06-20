<flux:card class="space-y-2">
    @if($listingCard)
        <x-listings.card :card="$listingCard" card-variant="waitlist" embedded :show-actions="false" />
    @else
        <flux:heading size="sm">{{ __('waitlist.title') }}</flux:heading>
    @endif

    <div class="grid grid-cols-2 gap-2 text-sm">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-zinc-500">{{ __('waitlist.desired_dates') }}</div>
            <div class="font-medium text-zinc-950 dark:text-zinc-50">
                {{ $item->desired_check_in_date?->format('d.m.Y') }} - {{ $item->desired_check_out_date?->format('d.m.Y') }}
            </div>
        </div>

        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-zinc-500">{{ __('waitlist.position') }}</div>
            <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $item->position ?: '—' }}</div>
        </div>
    </div>

    <div class="flex flex-wrap gap-1.5">
        <flux:badge size="sm">{{ __('waitlist.statuses.'.$item->status) }}</flux:badge>
        @if($item->max_price_per_night)
            <flux:badge size="sm">{{ __('waitlist.max_price') }}: {{ \Illuminate\Support\Number::currency((float) $item->max_price_per_night, $item->currency ?: 'EUR', app()->getLocale()) }}</flux:badge>
        @endif
    </div>
</flux:card>
