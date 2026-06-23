<div class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('guest_profile.intake.title') }}</span>
        </span>
    </flux:heading>
        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.intake.trip_purpose') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="tripPurpose" icon="briefcase" />
        <flux:error name="tripPurpose" />
    </flux:field>
</div>
