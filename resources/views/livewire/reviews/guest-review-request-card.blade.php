<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('reviews.actions.leave_review') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.review_requested') }}</flux:text>
    </div>

    <flux:button variant="primary" class="w-full sm:w-auto">
        {{ __('reviews.actions.leave_review') }}
    </flux:button>
</flux:card>
