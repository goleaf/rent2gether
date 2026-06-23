<form wire:submit="send" class="space-y-3">
    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('messages.fields.message') }}</span>
    </span>
</flux:label>
        <flux:textarea rows="3" wire:model="body" placeholder="{{ __('messages.messages.type_message') }}" />
        <flux:error name="body" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" icon="paper-airplane">
        <span wire:loading.remove wire:target="send">{{ __('messages.actions.send') }}</span>
        <span wire:loading wire:target="send">{{ __('messages.thread.actions.sending') }}</span>
    </flux:button>
</form>
