<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">

    {{-- Search bar --}}
    <div class="sticky top-0 z-10 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex flex-wrap gap-3 items-end">

                <flux:field class="flex-1 min-w-40">
                    <flux:label>City</flux:label>
                    <flux:input wire:model.live.debounce.400ms="city" placeholder="e.g. Berlin" icon="map-pin" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>Check-in</flux:label>
                    <flux:input type="date" wire:model.live="checkIn" :min="now()->toDateString()" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>Check-out</flux:label>
                    <flux:input type="date" wire:model.live="checkOut" :min="$checkIn ?: now()->addDay()->toDateString()" />
                </flux:field>

                <flux:field class="min-w-28">
                    <flux:label>Max €/night</flux:label>
                    <flux:input type="number" wire:model.live.debounce.400ms="priceMax" placeholder="Any" min="1" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>Sort</flux:label>
                    <flux:select wire:model.live="sort">
                        <flux:select.option value="price_asc">Cheapest first</flux:select.option>
                        <flux:select.option value="price_desc">Most expensive</flux:select.option>
                    </flux:select>
                </flux:field>

            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 flex gap-6">

        {{-- Sidebar filters --}}
        <aside class="hidden lg:block w-56 shrink-0">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-5 sticky top-28">

                <flux:heading size="sm">Filters</flux:heading>

                <flux:field>
                    <flux:label>Bed type</flux:label>
                    <flux:select wire:model.live="bedType">
                        <flux:select.option value="">Any type</flux:select.option>
                        @foreach($this->bedTypeOptions() as $value => $label)
                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Room gender</flux:label>
                    <flux:select wire:model.live="genderType">
                        <flux:select.option value="">Any</flux:select.option>
                        @foreach($this->genderOptions() as $value => $label)
                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:separator />

                <div class="space-y-3">
                    <flux:checkbox wire:model.live="instantOnly" label="Instant book only" />
                    <flux:checkbox wire:model.live="hasLocker" label="Personal locker" />
                    <flux:checkbox wire:model.live="hasWifi" label="Wi-Fi included" />
                </div>

                @if($city || $checkIn || $checkOut || $priceMax || $bedType || $genderType || $instantOnly || $hasLocker || $hasWifi)
                    <flux:button
                        variant="ghost" size="sm" class="w-full"
                        wire:click="$set('city', ''), $set('checkIn', ''), $set('checkOut', ''), $set('priceMax', ''), $set('bedType', ''), $set('genderType', ''), $set('instantOnly', false), $set('hasLocker', false), $set('hasWifi', false)"
                    >
                        Clear all filters
                    </flux:button>
                @endif
            </div>
        </aside>

        {{-- Results --}}
        <main class="flex-1 min-w-0">

            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" class="text-zinc-500">
                    @if($this->nights > 0)
                        {{ $this->nights }} {{ Str::plural('night', $this->nights) }} &middot;
                    @endif
                    {{ $this->beds->count() }} beds found
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
                    <flux:heading size="lg">No beds found</flux:heading>
                    <flux:text class="text-zinc-500 mt-2">Try adjusting the city, dates, or filters.</flux:text>
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
