<form wire:submit="save" class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="md">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.compatibility.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('guest_profile.compatibility.helper') }}</flux:text>
    </div>

        <flux:field variant="inline">
        <flux:checkbox wire:model.change="iLikeQuiet" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.compatibility.i_like_quiet') }}</span>
            </span>
        </flux:label>
        <flux:error name="iLikeQuiet" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="iWorkRemotely" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.compatibility.i_work_remotely') }}</span>
            </span>
        </flux:label>
        <flux:error name="iWorkRemotely" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="iNeedFastInternet" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.compatibility.i_need_fast_internet') }}</span>
            </span>
        </flux:label>
        <flux:error name="iNeedFastInternet" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="iAcceptLivingWithStrangers" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.compatibility.i_accept_living_with_strangers') }}</span>
            </span>
        </flux:label>
        <flux:error name="iAcceptLivingWithStrangers" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="iNeedLateEntry" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_profile.compatibility.i_need_late_entry') }}</span>
            </span>
        </flux:label>
        <flux:error name="iNeedLateEntry" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="check">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
