<section class="space-y-3">
    <flux:heading size="base">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('complaints.fields.desired_resolution') }}</span>
        </span>
    </flux:heading>
    <flux:button type="button" variant="ghost" icon="wrench">{{ __('complaints.resolution_types.fix_problem') }}</flux:button>
</section>
