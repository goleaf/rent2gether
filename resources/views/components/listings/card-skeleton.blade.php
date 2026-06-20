@props(['compact' => false])

<article {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900']) }} aria-label="{{ __('listing_card.loading') }}">
    <div class="{{ $compact ? 'h-36' : 'h-44' }} animate-pulse bg-zinc-100 dark:bg-zinc-800"></div>
    <div class="space-y-3 p-4">
        <div class="h-3 w-1/2 animate-pulse rounded bg-zinc-100 dark:bg-zinc-800"></div>
        <div class="h-5 w-4/5 animate-pulse rounded bg-zinc-100 dark:bg-zinc-800"></div>
        <div class="h-4 w-2/3 animate-pulse rounded bg-zinc-100 dark:bg-zinc-800"></div>
        <div class="h-14 animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
    </div>
</article>
