<div class="shrink-0">
    <flux:button
        size="xs"
        variant="ghost"
        wire:click="dismiss"
        wire:loading.attr="disabled"
        aria-label="{{ __('host_hints.dismiss') }}"
    >
        {{ __('host_hints.dismiss') }}
    </flux:button>
</div>
