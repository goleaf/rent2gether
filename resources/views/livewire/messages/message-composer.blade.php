<form wire:submit="send" class="space-y-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
    <flux:field>
        <flux:label>{{ __('messages.fields.message') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="body" placeholder="{{ __('messages.messages.type_message') }}" />
        <flux:error name="body" />
    </flux:field>

    <div class="flex items-center justify-between gap-3">
        <flux:checkbox wire:model.change="important" label="{{ __('messages.fields.is_important') }}" />
        <flux:button type="submit" variant="primary">
            <span wire:loading.remove wire:target="send">{{ __('messages.actions.send') }}</span>
            <span wire:loading wire:target="send">{{ __('messages.thread.actions.sending') }}</span>
        </flux:button>
    </div>
</form>
