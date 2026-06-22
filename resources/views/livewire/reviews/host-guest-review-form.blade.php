<form class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">{{ __('reviews.actions.review_guest') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.host_review_helper') }}</flux:text>
    </header>

    <livewire:reviews.review-score-group group="guest" />

    <flux:field>
        <flux:label>{{ __('reviews.fields.public_comment') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="comment" />
    </flux:field>

    <flux:checkbox :label="__('reviews.fields.recommend')" wire:model.change="recommend" />

    <flux:button type="button" variant="primary" class="w-full sm:w-auto">
        {{ __('reviews.actions.submit_review') }}
    </flux:button>
</form>
