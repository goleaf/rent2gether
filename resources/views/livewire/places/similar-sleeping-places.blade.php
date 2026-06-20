@php
    $money = static fn (float|int|string $amount, string $currency): string => \Illuminate\Support\Number::currency((float) $amount, $currency, app()->getLocale());
@endphp

<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($places as $place)
        <a href="{{ $place['href'] }}" wire:navigate class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition hover:border-emerald-300 dark:border-zinc-800 dark:bg-zinc-900">
            @if($place['image_url'])
                <img
                    src="{{ $place['image_url'] }}"
                    alt="{{ $place['image_alt'] }}"
                    width="480"
                    height="320"
                    loading="lazy"
                    decoding="async"
                    class="h-32 w-full bg-zinc-100 object-cover dark:bg-zinc-800"
                />
            @else
                <div class="flex h-32 w-full items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="home" class="size-8 text-zinc-300 dark:text-zinc-700" />
                </div>
            @endif

            <div class="space-y-2 p-3">
                <flux:heading size="sm" class="line-clamp-2">{{ $place['title'] }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">{{ $place['location'] }}</flux:text>
                <div class="text-sm font-semibold text-zinc-950 dark:text-zinc-50">
                    {{ $money($place['price'], $place['currency']) }}
                    <span class="font-normal text-zinc-500">{{ __('search.card.per_night') }}</span>
                </div>
            </div>
        </a>
    @empty
        <flux:card class="space-y-2 text-center sm:col-span-2 lg:col-span-3">
            <flux:icon name="magnifying-glass" class="mx-auto size-8 text-zinc-300 dark:text-zinc-700" />
            <flux:heading size="sm">{{ __('listing.detail.similar.empty_title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.similar.empty_helper') }}</flux:text>
        </flux:card>
    @endforelse
</div>
