<form class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">{{ __('reviews.actions.leave_review') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.double_blind_notice') }}</flux:text>
    </header>

    <livewire:reviews.review-score-group group="place" />

    <flux:field>
        <flux:label>{{ __('reviews.fields.what_liked') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="whatLiked" />
    </flux:field>

    <flux:button type="button" variant="primary" class="w-full sm:w-auto">
        {{ __('reviews.actions.submit_review') }}
    </flux:button>
</form>
