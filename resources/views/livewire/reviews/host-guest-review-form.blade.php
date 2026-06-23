<form class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="star" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('reviews.actions.review_guest') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.host_review_helper') }}</flux:text>
    </header>

    <livewire:reviews.review-score-group group="guest" />

    <flux:field>
        <flux:label>{{ __('reviews.fields.public_comment') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="comment" />
    </flux:field>

    <flux:checkbox :label="__('reviews.fields.recommend')" wire:model.change="recommend" />

    <flux:button type="button" variant="primary" class="w-full sm:w-auto" icon="eye">
        {{ __('reviews.actions.submit_review') }}
    </flux:button>
</form>
