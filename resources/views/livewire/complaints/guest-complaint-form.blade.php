<section class="mx-auto w-full max-w-md space-y-4 p-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('complaints.title') }}</flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('complaints.messages.evidence_helps') }}</flux:text>
    </div>

    <div class="grid gap-2">
        <flux:button type="button" variant="primary">{{ __('complaints.actions.submit_complaint') }}</flux:button>
        <flux:button type="button" variant="ghost">{{ __('complaints.actions.upload_evidence') }}</flux:button>
    </div>

    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('complaints.messages.unconfirmed_no_rating_impact') }}</flux:text>
</section>
