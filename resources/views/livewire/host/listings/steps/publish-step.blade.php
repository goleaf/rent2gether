<div class="space-y-4">
    <flux:card class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_publish.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_publish.helper') }}</flux:text>
    </flux:card>

    <livewire:host.listings.before-publish-checklist :property-id="$propertyId" :key="'before-publish-'.$propertyId" />

    <flux:card class="space-y-4">
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('listing_wizard.publish_step.ready') }}</flux:text>
                <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $readiness['ready'] ? __('listing_wizard.messages.ready_to_publish') : __('listing_wizard.messages.not_ready_to_publish') }}
                </p>
            </div>
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('listing_wizard.publish_step.review_status') }}</flux:text>
                <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $reviewStatus }}</p>
            </div>
        </div>

        @if($property->rejection_reason)
            <flux:callout variant="danger" icon="exclamation-triangle" :text="__('listing_wizard.publish_step.rejection_reason').': '.$property->rejection_reason" />
        @endif

        <flux:field variant="inline">
            <flux:checkbox wire:model.change="readyConfirmation" />
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing_wizard.publish_step.ready_confirmation') }}</span>
                </span>
            </flux:label>
            <flux:error name="readyConfirmation" />
        </flux:field>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="document-text" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing_wizard.publish_step.comment') }}</span>
                </span>
            </flux:label>
            <flux:textarea rows="3" wire:model.blur="comment" maxlength="1000" />
            <flux:error name="comment" />
        </flux:field>

        <flux:error name="publication" />

        <flux:button type="button" variant="primary" wire:click="sendToReview" wire:loading.attr="disabled" wire:target="sendToReview" icon="paper-airplane" class="w-full sm:w-auto">
            <span wire:loading.remove wire:target="sendToReview">{{ __('listing_wizard.publish_step.send_to_review') }}</span>
            <span wire:loading wire:target="sendToReview">{{ __('listing_wizard.actions.saving') }}</span>
        </flux:button>
    </flux:card>
</div>
