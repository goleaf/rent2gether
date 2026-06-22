<section>
    @if ($issues->isNotEmpty())
        <flux:card class="space-y-2">
            <flux:heading size="base">{{ __('inventory.panels.issue_notice') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.guest.issue_notice') }}</flux:text>
        </flux:card>
    @endif
</section>
