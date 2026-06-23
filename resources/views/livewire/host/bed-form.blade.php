<x-ui.page class="space-y-6">
    <flux:heading size="xl">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ $bed ? __('listing.form.edit_bed') : __('listing.form.new_bed') }}</span>
        </span>
    </flux:heading>
    <flux:text class="text-zinc-500">{{ $room->property->title }} &middot; {{ $room->title }}</flux:text>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing.form.basic_info') }}</span>
                </span>
            </flux:heading>
            <flux:input wire:model="title" label="{{ __('listing.form.title') }}" :error="$errors->first('title')" icon="tag" />
            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="type" label="{{ __('listing.form.type') }}">
                    @foreach($this->bedTypes() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input type="number" wire:model="maxGuests" label="{{ __('listing.form.max_guests') }}" min="1" icon="users" />
            </div>
            <flux:textarea wire:model.blur="description" label="{{ __('listing.form.description') }}" rows="2" />
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing.form.pricing') }}</span>
                </span>
            </flux:heading>
            <div class="grid grid-cols-2 gap-4">
                <flux:input type="number" wire:model="pricePerNight" label="{{ __('listing.form.price_per_night') }}" step="0.01" :error="$errors->first('pricePerNight')" icon="banknotes" />
                <flux:input type="number" wire:model="priceWeekend" label="{{ __('listing.form.weekend_price') }}" step="0.01" icon="banknotes" />
                <flux:input type="number" wire:model="priceWeekly" label="{{ __('listing.form.weekly_price') }}" step="0.01" icon="banknotes" />
                <flux:input type="number" wire:model="priceMonthly" label="{{ __('listing.form.monthly_price') }}" step="0.01" icon="banknotes" />
                <flux:input type="number" wire:model="cleaningFee" label="{{ __('listing.form.cleaning_fee') }}" step="0.01" icon="banknotes" />
                <flux:input type="number" wire:model="deposit" label="{{ __('listing.form.deposit') }}" step="0.01" icon="banknotes" />
                <flux:input type="number" wire:model="discountWeekly" label="{{ __('listing.form.weekly_discount') }}" step="0.1" icon="numbered-list" />
                <flux:input type="number" wire:model="discountMonthly" label="{{ __('listing.form.monthly_discount') }}" step="0.1" icon="numbered-list" />
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing.form.stay_rules') }}</span>
                </span>
            </flux:heading>
            <div class="grid grid-cols-2 gap-4">
                <flux:input type="number" wire:model="minNights" label="{{ __('listing.form.min_nights') }}" min="1" :error="$errors->first('minNights')" icon="numbered-list" />
                <flux:input type="number" wire:model="maxNights" label="{{ __('listing.form.max_nights') }}" icon="numbered-list" />
            </div>
                        <flux:field variant="inline">
                <flux:checkbox wire:model="instantBook" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing.form.instant_booking') }}</span>
                    </span>
                </flux:label>
                <flux:error name="instantBook" />
            </flux:field>
            <flux:select wire:model="cancellationPolicy" label="{{ __('listing.form.cancellation_policy') }}">
                @foreach($this->cancellationPolicies() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing.form.amenities') }}</span>
                </span>
            </flux:heading>
            <div class="grid grid-cols-2 gap-3">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model="hasLocker" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.locker') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasLocker" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model="hasOutlet" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.power_outlet') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasOutlet" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model="hasLamp" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.reading_lamp') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasLamp" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model="hasCurtain" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.privacy_curtain') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasCurtain" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model="hasShelf" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.shelf') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasShelf" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model="hasLuggageSpace" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.luggage_space') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasLuggageSpace" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model="hasLinen" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.bed_linen') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasLinen" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model="hasTowel" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.towel') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasTowel" />
                </flux:field>
            </div>
        </flux:card>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary" icon="calendar-days">{{ $bed ? __('app.actions.update') : __('app.actions.create') }}</flux:button>
            <flux:button href="{{ route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $room->property]) }}" variant="ghost" wire:navigate icon="x-mark">{{ __('app.actions.cancel') }}</flux:button>
        </div>
    </form>
</x-ui.page>
