<div>
    @auth
        @if($this->existingEntry)
            <flux:card class="space-y-2">
                <flux:text size="sm" class="text-green-600 font-medium">{{ __('You are on the waitlist') }}</flux:text>
                <flux:text size="sm" class="text-zinc-500">
                    {{ $this->existingEntry->desired_check_in }} - {{ $this->existingEntry->desired_check_out }}
                </flux:text>
                <flux:button size="sm" variant="ghost" wire:click="leave" wire:confirm="{{ __('Leave the waitlist?') }}">
                    {{ __('Leave waitlist') }}
                </flux:button>
            </flux:card>
        @elseif($showForm)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Join Waitlist') }}</flux:heading>
                <form wire:submit="join" class="space-y-3">
                    <flux:input type="date" wire:model="desiredCheckIn" label="{{ __('Desired check-in') }}" :error="$errors->first('desiredCheckIn')" />
                    <flux:input type="date" wire:model="desiredCheckOut" label="{{ __('Desired check-out') }}" :error="$errors->first('desiredCheckOut')" />
                    <flux:input type="number" wire:model="maxPrice" label="{{ __('Max price/night') }}" step="0.01" />
                    <flux:checkbox wire:model="autoRequest" label="{{ __('Auto-request when available') }}" />
                    <div class="flex gap-3">
                        <flux:button type="submit" variant="primary" size="sm">{{ __('Join') }}</flux:button>
                        <flux:button wire:click="$set('showForm', false)" variant="ghost" size="sm">{{ __('Cancel') }}</flux:button>
                    </div>
                </form>
            </flux:card>
        @else
            <flux:button size="sm" variant="ghost" icon="clock" wire:click="$set('showForm', true)">
                {{ __('Join waitlist') }}
            </flux:button>
        @endif
    @endauth
</div>
