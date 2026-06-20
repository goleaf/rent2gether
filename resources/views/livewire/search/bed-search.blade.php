<div class="min-h-screen space-y-4 bg-zinc-50 dark:bg-zinc-900">
    <section class="mx-auto max-w-7xl px-4 pt-4">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">{{ __('search.title') }}</flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('search.helper') }}</flux:text>
        </div>
    </section>

    {{-- Search bar --}}
    <div class="sticky top-0 z-10 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex flex-wrap gap-3 items-end">

                <flux:field class="flex-1 min-w-40">
                    <flux:label>{{ __('search.city') }}</flux:label>
                    <flux:input wire:model.live.debounce.500ms="city" placeholder="{{ __('search.city_placeholder') }}" icon="map-pin" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>{{ __('search.check_in') }}</flux:label>
                    <flux:input type="date" wire:model.change="checkIn" :min="now()->toDateString()" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>{{ __('search.check_out') }}</flux:label>
                    <flux:input type="date" wire:model.change="checkOut" :min="$checkIn ?: now()->addDay()->toDateString()" />
                </flux:field>

                <flux:field class="min-w-28">
                    <flux:label>{{ __('search.max_price') }}</flux:label>
                    <flux:input type="number" wire:model.blur="priceMax" :placeholder="__('search.any_gender')" min="1" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>{{ __('search.sort') }}</flux:label>
                    <flux:select wire:model.change="sort">
                        <flux:select.option value="price_asc">{{ __('search.sort_price_asc') }}</flux:select.option>
                        <flux:select.option value="price_desc">{{ __('search.sort_price_desc') }}</flux:select.option>
                    </flux:select>
                </flux:field>

            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 flex gap-6">

        {{-- Sidebar filters --}}
        <aside class="hidden lg:block w-56 shrink-0">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-5 sticky top-28">

                <flux:heading size="sm">{{ __('search.filters') }}</flux:heading>

                <flux:field>
                    <flux:label>{{ __('search.bed_type') }}</flux:label>
                    <flux:select wire:model.change="bedType">
                        <flux:select.option value="">{{ __('search.any_type') }}</flux:select.option>
                        @foreach($this->bedTypeOptions() as $value => $label)
                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('search.room_gender') }}</flux:label>
                    <flux:select wire:model.change="genderType">
                        <flux:select.option value="">{{ __('search.any_gender') }}</flux:select.option>
                        @foreach($this->genderOptions() as $value => $label)
                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:separator />

                <div class="space-y-3">
                    <flux:checkbox wire:model.change="instantOnly" label="{{ __('search.instant_only') }}" />
                    <flux:checkbox wire:model.change="hasLocker" label="{{ __('search.locker_only') }}" />
                    <flux:checkbox wire:model.change="hasWifi" label="{{ __('search.wifi_only') }}" />
                </div>

                @if($city || $checkIn || $checkOut || $priceMax || $bedType || $genderType || $instantOnly || $hasLocker || $hasWifi)
                    <flux:button
                        variant="ghost" size="sm" class="w-full"
                        wire:click="$set('city', ''), $set('checkIn', ''), $set('checkOut', ''), $set('priceMax', ''), $set('bedType', ''), $set('genderType', ''), $set('instantOnly', false), $set('hasLocker', false), $set('hasWifi', false)"
                    >
                        {{ __('search.clear_all') }}
                    </flux:button>
                @endif
            </div>
        </aside>

        {{-- Results --}}
        <main class="flex-1 min-w-0">

            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" class="text-zinc-500">
                    @if($this->nights > 0)
                        {{ __('search.results_nights', ['count' => $this->nights]) }} &middot;
                    @endif
                    {{ $this->beds->count() }} {{ __('search.found') }}
                </flux:text>

                @if($this->nights > 0)
                    <flux:badge color="blue" size="sm">
                        {{ $checkIn }} → {{ $checkOut }}
                    </flux:badge>
                @endif
            </div>

            @if($this->beds->isEmpty())
                <div class="text-center py-20">
                    <flux:icon name="magnifying-glass" class="mx-auto size-12 text-zinc-300 mb-4" />
                    <flux:heading size="lg">{{ __('search.no_results') }}</flux:heading>
                    <flux:text class="text-zinc-500 mt-2">{{ __('search.no_results_text') }}</flux:text>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($this->beds as $bed)
                        <livewire:search.bed-card :bed="$bed" :nights="$this->nights" :wire:key="'bed-'.$bed->id" />
                    @endforeach
                </div>

                <div class="mt-8">
                    <flux:pagination :paginator="$this->beds" />
                </div>
            @endif

        </main>
    </div>
</div>
