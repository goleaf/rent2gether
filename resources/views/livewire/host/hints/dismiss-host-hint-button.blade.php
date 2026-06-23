<div class="shrink-0">
    <flux:button
        size="xs"
        variant="ghost"
        wire:click="dismiss"
        wire:loading.attr="disabled"
        aria-label="{{ __('host_hints.dismiss') }}"
     icon="home-modern">
        {{ __('host_hints.dismiss') }}
    </flux:button>
</div>
