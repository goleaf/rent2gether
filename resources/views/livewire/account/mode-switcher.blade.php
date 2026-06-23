<div class="flex items-center rounded-lg bg-zinc-100 p-1 dark:bg-zinc-900" aria-label="{{ __('navigation.mode_switcher') }}">
    <flux:button
        type="button"
        size="xs"
        :variant="$mode === \App\Models\UserSetting::MODE_GUEST ? 'primary' : 'ghost'"
        wire:click="switchMode('{{ \App\Models\UserSetting::MODE_GUEST }}')"
        wire:loading.attr="disabled"
        class="min-h-8"
     icon="user">
        <span wire:loading.remove wire:target="switchMode">{{ __('navigation.guest_mode') }}</span>
        <span wire:loading wire:target="switchMode">{{ __('account.actions.switching') }}</span>
    </flux:button>

    <flux:button
        type="button"
        size="xs"
        :variant="$mode === \App\Models\UserSetting::MODE_HOST ? 'primary' : 'ghost'"
        wire:click="switchMode('{{ \App\Models\UserSetting::MODE_HOST }}')"
        wire:loading.attr="disabled"
        class="min-h-8"
     icon="user">
        <span wire:loading.remove wire:target="switchMode">{{ __('navigation.host_mode') }}</span>
        <span wire:loading wire:target="switchMode">{{ __('account.actions.switching') }}</span>
    </flux:button>
</div>
