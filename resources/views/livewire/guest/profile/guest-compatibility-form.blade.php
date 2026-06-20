<form wire:submit="save" class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="md">{{ __('guest_profile.compatibility.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('guest_profile.compatibility.helper') }}</flux:text>
    </div>

    <flux:checkbox wire:model.change="iLikeQuiet" :label="__('guest_profile.compatibility.i_like_quiet')" />
    <flux:checkbox wire:model.change="iWorkRemotely" :label="__('guest_profile.compatibility.i_work_remotely')" />
    <flux:checkbox wire:model.change="iNeedFastInternet" :label="__('guest_profile.compatibility.i_need_fast_internet')" />
    <flux:checkbox wire:model.change="iAcceptLivingWithStrangers" :label="__('guest_profile.compatibility.i_accept_living_with_strangers')" />
    <flux:checkbox wire:model.change="iNeedLateEntry" :label="__('guest_profile.compatibility.i_need_late_entry')" />

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
