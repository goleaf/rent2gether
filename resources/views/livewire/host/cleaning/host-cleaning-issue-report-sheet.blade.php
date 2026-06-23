<section class="space-y-3">
    <flux:card class="space-y-2">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('cleaning.sections.issues') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('cleaning.helpers.finding_sheet') }}
        </flux:text>
    </flux:card>

    <div class="flex flex-wrap gap-2">
        @foreach (['damage_found', 'extra_dirt_found', 'forgotten_items_found', 'needs_repair'] as $issueType)
            <flux:badge color="zinc" icon="user">{{ __('cleaning.issue_types.'.$issueType) }}</flux:badge>
        @endforeach
    </div>
</section>
