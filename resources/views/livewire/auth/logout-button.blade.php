<flux:menu.item
    icon="arrow-right-start-on-rectangle"
    type="button"
    wire:click="logout"
    wire:loading.attr="disabled"
    wire:target="logout"
>
    {{ __('navigation.logout') }}
</flux:menu.item>
