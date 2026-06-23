<flux:button
    type="button"
    variant="{{ $joined ? 'filled' : 'primary' }}"
    size="sm"
    icon="clock"
    wire:click="join"
    wire:loading.attr="disabled"
    wire:target="join"
>
    <span wire:loading.remove wire:target="join">
        {{ $offered ? __('waitlist.states.offered') : ($joined ? __('waitlist.joined') : __('waitlist.join')) }}
    </span>
    <span wire:loading wire:target="join">{{ __('waitlist.states.saving') }}</span>
</flux:button>
