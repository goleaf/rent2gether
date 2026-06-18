<div>
    @auth
        <flux:button size="sm" variant="ghost" icon="bookmark" wire:click="$set('showModal', true)">
            {{ __('Save search') }}
        </flux:button>

        @if($showModal)
            <flux:modal wire:model="showModal">
                <flux:heading size="lg">{{ __('Save this search') }}</flux:heading>
                <form wire:submit="save" class="space-y-4 mt-4">
                    <flux:input wire:model="name" label="{{ __('Name') }}" placeholder="{{ __('e.g. Berlin summer beds') }}" :error="$errors->first('name')" />
                    <div class="text-sm text-zinc-500 space-y-1">
                        @if($city)<div>{{ __('City') }}: {{ $city }}</div>@endif
                        @if($checkIn)<div>{{ __('Dates') }}: {{ $checkIn }} - {{ $checkOut }}</div>@endif
                        @if($minPrice || $maxPrice)<div>{{ __('Price') }}: &euro;{{ $minPrice ?? 0 }} - &euro;{{ $maxPrice ?? '∞' }}</div>@endif
                    </div>
                    <div class="flex gap-3">
                        <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                        <flux:button wire:click="$set('showModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endif
    @endauth
</div>
