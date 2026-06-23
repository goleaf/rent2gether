<div>
    @auth
        <flux:button size="sm" variant="ghost" icon="eye" wire:click="$set('showModal', true)">
            {{ __('search.saved.save_title') }}
        </flux:button>

        @if($showModal)
            <flux:modal wire:model="showModal">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="heart" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.saved.save_title') }}</span>
                    </span>
                </flux:heading>
                <form wire:submit="save" class="space-y-4 mt-4">
                                        <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="heart" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('app.profile.name') }}</span>
                            </span>
                        </flux:label>
                        <flux:input wire:model="name" placeholder="{{ __('search.saved.name_placeholder') }}" :error="$errors->first('name')" icon="magnifying-glass" />
                        <flux:error name="name" />
                    </flux:field>
                    <div class="text-sm text-zinc-500 space-y-1">
                        @if($city)<div>{{ __('listing.form.city') }}: {{ $city }}</div>@endif
                        @if($checkIn)<div>{{ __('search.saved.dates') }}: {{ $checkIn }} - {{ $checkOut }}</div>@endif
                        @if($minPrice || $maxPrice)<div>{{ __('search.saved.price') }}: &euro;{{ $minPrice ?? 0 }} - &euro;{{ $maxPrice ?? '∞' }}</div>@endif
                    </div>
                    <div class="flex gap-3">
                        <flux:button type="submit" variant="primary" icon="check">{{ __('app.actions.save') }}</flux:button>
                        <flux:button wire:click="$set('showModal', false)" variant="ghost" icon="x-mark">{{ __('app.actions.cancel') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endif
    @endauth
</div>
