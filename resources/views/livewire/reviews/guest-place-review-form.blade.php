<form class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="star" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('reviews.actions.leave_review') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.double_blind_notice') }}</flux:text>
    </header>

    <livewire:reviews.review-score-group group="place" />

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="star" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('reviews.fields.what_liked') }}</span>
    </span>
</flux:label>
        <flux:textarea rows="3" wire:model.blur="whatLiked" />
    </flux:field>

    <flux:button type="button" variant="primary" class="w-full sm:w-auto" icon="eye">
        {{ __('reviews.actions.submit_review') }}
    </flux:button>
</form>
