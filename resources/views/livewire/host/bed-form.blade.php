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
                        <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing.form.title') }}</span>
                    </span>
                </flux:label>
                <flux:input wire:model="title" :error="$errors->first('title')" icon="tag" />
                <flux:error name="title" />
            </flux:field>
            <div class="grid grid-cols-2 gap-4">
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.type') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model="type">
                    @foreach($this->bedTypes() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                    <flux:error name="type" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.max_guests') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="maxGuests" min="1" icon="users" />
                    <flux:error name="maxGuests" />
                </flux:field>
            </div>
                        <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing.form.description') }}</span>
                    </span>
                </flux:label>
                <flux:textarea wire:model.blur="description" rows="2" />
                <flux:error name="description" />
            </flux:field>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing.form.pricing') }}</span>
                </span>
            </flux:heading>
            <div class="grid grid-cols-2 gap-4">
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.price_per_night') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="pricePerNight" step="0.01" :error="$errors->first('pricePerNight')" icon="banknotes" />
                    <flux:error name="pricePerNight" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.weekend_price') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="priceWeekend" step="0.01" icon="banknotes" />
                    <flux:error name="priceWeekend" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.weekly_price') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="priceWeekly" step="0.01" icon="banknotes" />
                    <flux:error name="priceWeekly" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.monthly_price') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="priceMonthly" step="0.01" icon="banknotes" />
                    <flux:error name="priceMonthly" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.cleaning_fee') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="cleaningFee" step="0.01" icon="banknotes" />
                    <flux:error name="cleaningFee" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.deposit') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="deposit" step="0.01" icon="banknotes" />
                    <flux:error name="deposit" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.weekly_discount') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="discountWeekly" step="0.1" icon="numbered-list" />
                    <flux:error name="discountWeekly" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.monthly_discount') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="discountMonthly" step="0.1" icon="numbered-list" />
                    <flux:error name="discountMonthly" />
                </flux:field>
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
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.min_nights') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="minNights" min="1" :error="$errors->first('minNights')" icon="numbered-list" />
                    <flux:error name="minNights" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing.form.max_nights') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="maxNights" icon="numbered-list" />
                    <flux:error name="maxNights" />
                </flux:field>
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
                        <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing.form.cancellation_policy') }}</span>
                    </span>
                </flux:label>
                <flux:select wire:model="cancellationPolicy">
                @foreach($this->cancellationPolicies() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
                <flux:error name="cancellationPolicy" />
            </flux:field>
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
