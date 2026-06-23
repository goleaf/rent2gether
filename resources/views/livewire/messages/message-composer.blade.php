<form wire:submit="send" class="space-y-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
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

    <div class="flex items-center justify-between gap-3">
                <flux:field variant="inline">
            <flux:checkbox wire:model.change="important" />
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('messages.fields.is_important') }}</span>
                </span>
            </flux:label>
            <flux:error name="important" />
        </flux:field>
        <flux:button type="submit" variant="primary" icon="paper-airplane">
            <span wire:loading.remove wire:target="send">{{ __('messages.actions.send') }}</span>
            <span wire:loading wire:target="send">{{ __('messages.thread.actions.sending') }}</span>
        </flux:button>
    </div>
</form>
