<x-ui.section class="space-y-4 p-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('complaints.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('complaints.messages.evidence_helps') }}</flux:text>
    </div>

    <div class="grid gap-2">
        <flux:button type="button" variant="primary" icon="check">{{ __('complaints.actions.submit_complaint') }}</flux:button>
        <flux:button type="button" variant="ghost" icon="camera">{{ __('complaints.actions.upload_evidence') }}</flux:button>
    </div>

    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('complaints.messages.unconfirmed_no_rating_impact') }}</flux:text>
</x-ui.section>
