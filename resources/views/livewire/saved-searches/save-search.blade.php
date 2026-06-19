<div>
    @auth
        <flux:button size="sm" variant="ghost" icon="bookmark" wire:click="$set('showModal', true)">
            {{ __('search.saved.save_title') }}
        </flux:button>

        @if($showModal)
            <flux:modal wire:model="showModal">
                <flux:heading size="lg">{{ __('search.saved.save_title') }}</flux:heading>
                <form wire:submit="save" class="space-y-4 mt-4">
                    <flux:input wire:model="name" label="{{ __('app.profile.name') }}" placeholder="{{ __('search.saved.name_placeholder') }}" :error="$errors->first('name')" />
                    <div class="text-sm text-zinc-500 space-y-1">
                        @if($city)<div>{{ __('listing.form.city') }}: {{ $city }}</div>@endif
                        @if($checkIn)<div>{{ __('search.saved.dates') }}: {{ $checkIn }} - {{ $checkOut }}</div>@endif
                        @if($minPrice || $maxPrice)<div>{{ __('search.saved.price') }}: &euro;{{ $minPrice ?? 0 }} - &euro;{{ $maxPrice ?? '∞' }}</div>@endif
                    </div>
                    <div class="flex gap-3">
                        <flux:button type="submit" variant="primary">{{ __('app.actions.save') }}</flux:button>
                        <flux:button wire:click="$set('showModal', false)" variant="ghost">{{ __('app.actions.cancel') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endif
    @endauth
</div>
