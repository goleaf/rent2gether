<section class="grid grid-cols-2 gap-2">
    <flux:button variant="primary" wire:loading.attr="disabled">{{ __('inspections.actions.pass') }}</flux:button>
    <flux:button variant="ghost" wire:loading.attr="disabled">{{ __('inspections.actions.fail') }}</flux:button>
</section>
