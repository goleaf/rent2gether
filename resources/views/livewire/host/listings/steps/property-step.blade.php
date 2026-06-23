<flux:card class="space-y-4">
    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_wizard.property.name') }}</span>
    </span>
</flux:label>
        <flux:input wire:model.blur="title" icon="tag" />
    </flux:field>

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_wizard.property.district') }}</span>
    </span>
</flux:label>
        <flux:input wire:model.blur="district" icon="map-pin" />
    </flux:field>

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_wizard.property.description') }}</span>
    </span>
</flux:label>
        <flux:textarea rows="4" wire:model.blur="description" />
    </flux:field>

    <flux:button type="button" variant="primary" wire:click="save" wire:loading.attr="disabled" icon="bookmark">
        {{ __('listing_wizard.save_draft') }}
    </flux:button>
</flux:card>
