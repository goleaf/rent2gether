<div class="shrink-0">
    <flux:button
        type="button"
        size="xs"
        variant="ghost"
        icon="x-mark"
        wire:click="dismiss"
        wire:loading.attr="disabled"
        wire:target="dismiss"
        aria-label="{{ __('guest_hints.dismiss') }}"
    />
    <flux:error name="hint" />
</div>
