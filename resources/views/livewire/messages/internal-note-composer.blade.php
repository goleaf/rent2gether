<form wire:submit="save" class="space-y-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
    <flux:callout color="zinc" icon="lock-closed">
        <flux:callout.text>{{ __('messages.messages.internal_note_hidden') }}</flux:callout.text>
    </flux:callout>

    <flux:field>
        <flux:label>{{ __('messages.fields.internal_note') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="note" />
        <flux:error name="note" />
    </flux:field>

    <flux:button type="submit" variant="primary">{{ __('messages.actions.add_internal_note') }}</flux:button>
</form>
