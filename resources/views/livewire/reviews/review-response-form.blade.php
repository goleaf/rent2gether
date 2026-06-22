<form class="space-y-3">
    <flux:field>
        <flux:label>{{ __('reviews.actions.respond_to_review') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="responseText" />
    </flux:field>

    <flux:button type="button" variant="primary" class="w-full sm:w-auto">
        {{ __('reviews.actions.publish_response') }}
    </flux:button>
</form>
