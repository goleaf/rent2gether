<button wire:click="toggle" class="group" title="{{ $isFavorited ? __('search.favorites.remove') : __('search.favorites.add') }}">
    <flux:icon name="heart" class="size-5 transition {{ $isFavorited ? 'text-red-500 fill-red-500' : 'text-zinc-400 group-hover:text-red-400' }}" />
</button>
