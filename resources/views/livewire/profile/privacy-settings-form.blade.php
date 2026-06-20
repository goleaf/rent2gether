<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">{{ __('privacy.title') }}</flux:heading>

    <flux:checkbox wire:model.change="showRealName" :label="__('privacy.fields.show_real_name')" />
    <flux:checkbox wire:model.change="showCity" :label="__('privacy.fields.show_city')" />
    <flux:checkbox wire:model.change="showLanguages" :label="__('privacy.fields.show_languages')" />
    <flux:checkbox wire:model.change="showPhoneAfterBooking" :label="__('privacy.fields.show_phone_after_booking')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
