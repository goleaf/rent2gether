<flux:card class="space-y-4">
    <flux:field>
        <flux:label>{{ __('listing_wizard.property.name') }}</flux:label>
        <flux:input wire:model.blur="title" icon="tag" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('listing_wizard.property.district') }}</flux:label>
        <flux:input wire:model.blur="district" icon="map-pin" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('listing_wizard.property.description') }}</flux:label>
        <flux:textarea rows="4" wire:model.blur="description" />
    </flux:field>

    <flux:button type="button" variant="primary" wire:click="save" wire:loading.attr="disabled" icon="bookmark">
        {{ __('listing_wizard.save_draft') }}
    </flux:button>
</flux:card>
