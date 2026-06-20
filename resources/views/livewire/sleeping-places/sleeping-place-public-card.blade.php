<div>
    @if($this->card)
        <x-listings.card :card="$this->card" card-variant="search" />
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
            {{ __('sleeping_places.empty.not_found') }}
        </div>
    @endif
</div>
