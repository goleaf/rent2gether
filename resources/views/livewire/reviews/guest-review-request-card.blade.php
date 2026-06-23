<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="star" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('reviews.actions.leave_review') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.review_requested') }}</flux:text>
    </div>

    <flux:button variant="primary" class="w-full sm:w-auto" icon="eye">
        {{ __('reviews.actions.leave_review') }}
    </flux:button>
</flux:card>
