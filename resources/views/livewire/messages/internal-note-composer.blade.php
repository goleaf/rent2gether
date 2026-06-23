<form wire:submit="save" class="space-y-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
    <flux:callout color="zinc" icon="information-circle">
        <flux:callout.text>{{ __('messages.messages.internal_note_hidden') }}</flux:callout.text>
    </flux:callout>

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('messages.fields.internal_note') }}</span>
    </span>
</flux:label>
        <flux:textarea rows="3" wire:model.blur="note" />
        <flux:error name="note" />
    </flux:field>

    <flux:button type="submit" variant="primary" icon="chat-bubble-left-right">{{ __('messages.actions.add_internal_note') }}</flux:button>
</form>
