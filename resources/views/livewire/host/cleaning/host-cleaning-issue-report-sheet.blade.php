<section class="space-y-3">
    <flux:card class="space-y-2">
        <flux:heading size="lg">{{ __('cleaning.sections.issues') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('cleaning.helpers.finding_sheet') }}
        </flux:text>
    </flux:card>

    <div class="flex flex-wrap gap-2">
        @foreach (['damage_found', 'extra_dirt_found', 'forgotten_items_found', 'needs_repair'] as $issueType)
            <flux:badge color="zinc">{{ __('cleaning.issue_types.'.$issueType) }}</flux:badge>
        @endforeach
    </div>
</section>
