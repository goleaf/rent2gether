<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('privacy.title') }}</span>
        </span>
    </flux:heading>

        <flux:field variant="inline">
        <flux:checkbox wire:model.change="showRealName" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('privacy.fields.show_real_name') }}</span>
            </span>
        </flux:label>
        <flux:error name="showRealName" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="showCity" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('privacy.fields.show_city') }}</span>
            </span>
        </flux:label>
        <flux:error name="showCity" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="showLanguages" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('privacy.fields.show_languages') }}</span>
            </span>
        </flux:label>
        <flux:error name="showLanguages" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="showPhoneAfterBooking" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('privacy.fields.show_phone_after_booking') }}</span>
            </span>
        </flux:label>
        <flux:error name="showPhoneAfterBooking" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
