@props(['compact' => false])

<article {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900']) }} aria-label="{{ __('listing_card.loading') }}">
    <flux:skeleton.group animate="shimmer">
        <flux:skeleton class="{{ $compact ? 'h-36' : 'h-44' }} w-full rounded-none" />
        <div class="space-y-3 p-4">
            <flux:skeleton.line class="w-1/2" />
            <flux:skeleton.line class="w-4/5" />
            <flux:skeleton.line class="w-2/3" />
            <flux:skeleton class="h-14 w-full rounded-lg" />
        </div>
    </flux:skeleton.group>
</article>
