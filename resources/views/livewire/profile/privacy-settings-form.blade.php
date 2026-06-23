<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('privacy.title') }}</span>
        </span>
    </flux:heading>

    <flux:checkbox wire:model.change="showRealName" :label="__('privacy.fields.show_real_name')" />
    <flux:checkbox wire:model.change="showCity" :label="__('privacy.fields.show_city')" />
    <flux:checkbox wire:model.change="showLanguages" :label="__('privacy.fields.show_languages')" />
    <flux:checkbox wire:model.change="showPhoneAfterBooking" :label="__('privacy.fields.show_phone_after_booking')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
