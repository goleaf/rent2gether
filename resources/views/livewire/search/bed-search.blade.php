<x-ui.page>
    <x-ui.section>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="magnifying-glass" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('search.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('search.helper') }}</flux:text>
        </div>
    </x-ui.section>

    {{-- Search bar --}}
    <flux:card class="sticky top-0 z-10 p-0">
        <div class="p-4">
            <div class="flex flex-wrap gap-3 items-end">

                <flux:field class="flex-1 min-w-40">
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.city') }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.live.debounce.500ms="city" placeholder="{{ __('search.city_placeholder') }}" icon="map-pin" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.check_in') }}</span>
    </span>
</flux:label>
                    <flux:input type="date" wire:model.change="checkIn" :min="now()->toDateString()" icon="calendar-days" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.check_out') }}</span>
    </span>
</flux:label>
                    <flux:input type="date" wire:model.change="checkOut" :min="$checkIn ?: now()->addDay()->toDateString()" icon="calendar-days" />
                </flux:field>

                <flux:field class="min-w-28">
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.max_price') }}</span>
    </span>
</flux:label>
                    <flux:input type="number" wire:model.blur="priceMax" :placeholder="__('search.any_gender')" min="1" icon="banknotes" />
                </flux:field>

                <flux:field class="min-w-36">
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.sort') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="sort">
                        <flux:select.option value="price_asc">{{ __('search.sort_price_asc') }}</flux:select.option>
                        <flux:select.option value="price_desc">{{ __('search.sort_price_desc') }}</flux:select.option>
                    </flux:select>
                </flux:field>

            </div>
        </div>
    </flux:card>

    <div class="flex gap-6">

        {{-- Sidebar filters --}}
        <aside class="hidden lg:block w-56 shrink-0">
            <flux:card class="sticky top-28 space-y-5">

                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters') }}</span>
                    </span>
                </flux:heading>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.bed_type') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="bedType">
                        <flux:select.option value="">{{ __('search.any_type') }}</flux:select.option>
                        @foreach($this->bedTypeOptions() as $value => $label)
                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.room_gender') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="genderType">
                        <flux:select.option value="">{{ __('search.any_gender') }}</flux:select.option>
                        @foreach($this->genderOptions() as $value => $label)
                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:separator />

                <div class="space-y-3">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="instantOnly" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.instant_only') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="instantOnly" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="hasLocker" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.locker_only') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="hasLocker" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="hasWifi" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.wifi_only') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="hasWifi" />
                    </flux:field>
                </div>

                @if($city || $checkIn || $checkOut || $priceMax || $bedType || $genderType || $instantOnly || $hasLocker || $hasWifi)
                    <flux:button
                        variant="ghost" size="sm" class="w-full"
                        wire:click="$set('city', ''), $set('checkIn', ''), $set('checkOut', ''), $set('priceMax', ''), $set('bedType', ''), $set('genderType', ''), $set('instantOnly', false), $set('hasLocker', false), $set('hasWifi', false)"
                     icon="x-mark">
                        {{ __('search.clear_all') }}
                    </flux:button>
                @endif
            </flux:card>
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
                    <flux:badge color="blue" size="sm" icon="calendar-days">
                        {{ $checkIn }} → {{ $checkOut }}
                    </flux:badge>
                @endif
            </div>

            @if($this->beds->isEmpty())
                <div class="text-center py-20">
                    <flux:icon name="magnifying-glass" class="mx-auto size-12 text-zinc-300 mb-4" />
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="magnifying-glass" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('search.no_results') }}</span>
                        </span>
                    </flux:heading>
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
</x-ui.page>
