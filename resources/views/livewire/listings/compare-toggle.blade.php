<flux:button
    type="button"
    size="sm"
    variant="{{ $selected ? 'primary' : 'ghost' }}"
    icon="scale"
    wire:click="toggle"
    wire:loading.attr="disabled"
    wire:target="toggle"
    aria-label="{{ $selected ? __('listing_card.remove_from_compare') : __('listing_card.compare') }}"
>
    <span wire:loading.remove wire:target="toggle">{{ $selected ? __('listing_card.in_comparison') : __('listing_card.compare') }}</span>
    <span wire:loading wire:target="toggle">{{ __('listing_card.updating') }}</span>
</flux:button>
