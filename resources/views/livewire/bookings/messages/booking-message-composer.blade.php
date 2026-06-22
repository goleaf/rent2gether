<form wire:submit="send" class="space-y-3">
    <flux:field>
        <flux:label>{{ __('messages.fields.message') }}</flux:label>
        <flux:textarea rows="3" wire:model="body" placeholder="{{ __('messages.messages.type_message') }}" />
        <flux:error name="body" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full">
        <span wire:loading.remove wire:target="send">{{ __('messages.actions.send') }}</span>
        <span wire:loading wire:target="send">{{ __('messages.thread.actions.sending') }}</span>
    </flux:button>
</form>
