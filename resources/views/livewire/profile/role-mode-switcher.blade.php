<div class="grid grid-cols-3 gap-2">
    <flux:button type="button" wire:click="switchMode('guest')" wire:loading.class="opacity-50" icon="map-pin">
        {{ __('users.role_modes.guest') }}
    </flux:button>
    <flux:button type="button" wire:click="switchMode('host')" wire:loading.class="opacity-50" icon="map-pin">
        {{ __('users.role_modes.host') }}
    </flux:button>
    <flux:button type="button" wire:click="switchMode('guest_host')" wire:loading.class="opacity-50" icon="map-pin">
        {{ __('users.role_modes.guest_host') }}
    </flux:button>
</div>
