<flux:button
    type="button"
    size="sm"
    variant="{{ $selected ? 'primary' : 'ghost' }}"
    icon="heart"
    wire:click="toggle"
    wire:loading.attr="disabled"
    wire:target="toggle"
    aria-label="{{ $selected ? __('favorites.remove') : __('favorites.add') }}"
>
    <span wire:loading.remove wire:target="toggle">{{ $selected ? __('favorites.saved') : __('favorites.add') }}</span>
    <span wire:loading wire:target="toggle">{{ __('favorites.saving') }}</span>
</flux:button>
