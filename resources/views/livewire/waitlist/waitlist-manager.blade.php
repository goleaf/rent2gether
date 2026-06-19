<div>
    @auth
        @if($this->existingEntry)
            <flux:card class="space-y-2">
                <flux:text size="sm" class="text-green-600 font-medium">{{ __('search.waitlist.on_list') }}</flux:text>
                <flux:text size="sm" class="text-zinc-500">
                    {{ $this->existingEntry->desired_check_in }} - {{ $this->existingEntry->desired_check_out }}
                </flux:text>
                <flux:button size="sm" variant="ghost" wire:click="leave" wire:confirm="{{ __('search.waitlist.leave_confirmation') }}">
                    {{ __('search.waitlist.leave') }}
                </flux:button>
            </flux:card>
        @elseif($showForm)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('search.waitlist.join_title') }}</flux:heading>
                <form wire:submit="join" class="space-y-3">
                    <flux:input type="date" wire:model="desiredCheckIn" label="{{ __('search.waitlist.desired_check_in') }}" :error="$errors->first('desiredCheckIn')" />
                    <flux:input type="date" wire:model="desiredCheckOut" label="{{ __('search.waitlist.desired_check_out') }}" :error="$errors->first('desiredCheckOut')" />
                    <flux:input type="number" wire:model="maxPrice" label="{{ __('search.waitlist.max_price') }}" step="0.01" />
                    <flux:checkbox wire:model="autoRequest" label="{{ __('search.waitlist.auto_request_when_available') }}" />
                    <div class="flex gap-3">
                        <flux:button type="submit" variant="primary" size="sm">{{ __('app.actions.join') }}</flux:button>
                        <flux:button wire:click="$set('showForm', false)" variant="ghost" size="sm">{{ __('app.actions.cancel') }}</flux:button>
                    </div>
                </form>
            </flux:card>
        @else
            <flux:button size="sm" variant="ghost" icon="clock" wire:click="$set('showForm', true)">
                {{ __('search.waitlist.join_title') }}
            </flux:button>
        @endif
    @endauth
</div>
