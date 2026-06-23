<form class="space-y-3">
    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('reviews.actions.respond_to_review') }}</span>
    </span>
</flux:label>
        <flux:textarea rows="3" wire:model.blur="responseText" />
    </flux:field>

    <flux:button type="button" variant="primary" class="w-full sm:w-auto" icon="eye">
        {{ __('reviews.actions.publish_response') }}
    </flux:button>
</form>
