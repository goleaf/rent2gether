@props(['card'])

@php
    $currency = $card['currency'] ?: 'EUR';
    $money = static fn (float|int|string $amount): string => \Illuminate\Support\Number::currency((float) $amount, $currency, app()->getLocale());
@endphp

<div class="space-y-2 rounded-lg bg-zinc-50 px-3 py-3 dark:bg-zinc-950/60">
    <div class="flex items-end justify-between gap-3">
        <div>
            <div class="text-lg font-semibold text-zinc-950 dark:text-zinc-50">
                {{ __('listing_card.price_per_night', ['price' => $money($card['price_per_night'])]) }}
            </div>
            @if($card['total_price'] === null)
                <div class="text-xs text-zinc-500">{{ __('listing_card.choose_dates_for_total') }}</div>
            @endif
        </div>

        @if($card['total_price'] !== null && ($card['nights_count'] ?? 0) > 0)
            <div class="text-right">
                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                    {{ $money($card['total_price']) }}
                </div>
                <div class="text-xs text-zinc-500">
                    {{ trans_choice('listing_card.total_for_nights', (int) $card['nights_count'], ['price' => $money($card['total_price']), 'count' => (int) $card['nights_count']]) }}
                </div>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
        @if($card['has_deposit'])
            <span>{{ __('listing_card.deposit', ['amount' => $money($card['deposit_amount'])]) }}</span>
        @else
            <span>{{ __('listing_card.no_deposit') }}</span>
        @endif

        @if($card['has_free_cancellation'])
            <span>{{ __('listing_card.free_cancellation') }}</span>
        @endif

        <span>{{ $card['availability_message'] }}</span>
    </div>
</div>
