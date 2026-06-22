<section class="mx-auto w-full max-w-md space-y-4 p-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('complaints.host_title') }}</flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('complaints.messages.other_party_notified') }}</flux:text>
    </div>

    <div class="grid gap-2">
        <flux:button type="button" variant="primary">{{ __('complaints.actions.respond') }}</flux:button>
        <flux:button type="button" variant="ghost">{{ __('complaints.actions.offer_relocation') }}</flux:button>
        <flux:button type="button" variant="ghost">{{ __('complaints.actions.offer_refund') }}</flux:button>
    </div>
</section>
