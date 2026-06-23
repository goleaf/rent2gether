<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('host.sleeping_place_wizard.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ $sleepingPlaceId ? __('host.sleeping_place_wizard.edit_heading') : __('host.sleeping_place_wizard.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            {{ __('host.sleeping_place_wizard.helper', ['room' => $this->room->title]) }}
        </flux:text>
    </section>

    @if($wasSaved)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ __('host.sleeping_place_wizard.saved_notice') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm" class="font-medium text-zinc-700 dark:text-zinc-200">
                {{ __('host.sleeping_place_wizard.progress', ['current' => $step, 'total' => 7]) }}
            </flux:text>
            <flux:badge size="sm" icon="clock">{{ $status ? __('statuses.sleeping_place.'.$status) : __('statuses.sleeping_place.draft') }}</flux:badge>
        </div>

        <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ (int) (($step / 7) * 100) }}%"></div>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach($this->wizardSteps() as $wizardStep)
                <flux:button
                    type="button"
                    size="xs"
                    variant="{{ $step === $wizardStep['number'] ? 'primary' : 'outline' }}"
                    wire:click="$set('step', {{ $wizardStep['number'] }})"
                    class="shrink-0"
                    tooltip="{{ $wizardStep['title'] }}"
                    aria-current="{{ $step === $wizardStep['number'] ? 'step' : 'false' }}"
                 icon="cursor-arrow-rays">
                    {{ $wizardStep['number'] }}
                </flux:button>
            @endforeach
        </div>
    </flux:card>

    @if($sleepingPlaceId)
        <livewire:host.hints.host-wizard-hints
            target-type="sleeping_place"
            :target-id="$sleepingPlaceId"
            :step="$this->hostHintStep()"
            :key="'host-wizard-hints-'.$sleepingPlaceId.'-'.$step"
        />
    @endif

    <form wire:submit="publish" class="space-y-5">
        <flux:card class="space-y-5">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.sleeping_place_wizard.steps.'.$step.'.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('host.sleeping_place_wizard.steps.'.$step.'.helper') }}
                </flux:text>
            </div>

            @switch($step)
                @case(1)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.place_number') }}</span>
    </span>
</flux:label>
                            <flux:input wire:model.blur="placeNumber" icon="pencil-square" />
                            <flux:error name="placeNumber" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.display_name') }}</span>
    </span>
</flux:label>
                            <flux:input wire:model.blur="displayName" icon="user" />
                            <flux:error name="displayName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.type') }}</span>
    </span>
</flux:label>
                            <flux:select wire:model.change="type">
                                @foreach($this->typeOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="type" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.status') }}</span>
    </span>
</flux:label>
                            <flux:select wire:model.change="status">
                                @foreach($this->statusOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="status" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.bunk_level') }}</span>
    </span>
</flux:label>
                            <flux:input wire:model.blur="bunkLevel" icon="pencil-square" />
                            <flux:error name="bunkLevel" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.length_cm') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="lengthCm" icon="numbered-list" />
                            <flux:error name="lengthCm" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.width_cm') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="widthCm" icon="numbered-list" />
                            <flux:error name="widthCm" />
                        </flux:field>
                    </div>
                    @break

                @case(2)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.mattress_type') }}</span>
    </span>
</flux:label>
                            <flux:input wire:model.blur="mattressType" icon="pencil-square" />
                            <flux:error name="mattressType" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.mattress_firmness') }}</span>
    </span>
</flux:label>
                            <flux:select wire:model.change="mattressFirmness">
                                <flux:select.option value="">{{ __('host.sleeping_place_wizard.options.not_specified') }}</flux:select.option>
                                @foreach($this->firmnessOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="mattressFirmness" />
                        </flux:field>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasPillow" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_pillow') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasPillow" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasBlanket" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_blanket') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasBlanket" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasBedding" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_bedding') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasBedding" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasTowel" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_towel') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasTowel" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasCurtain" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_curtain') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasCurtain" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasLamp" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_lamp') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasLamp" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasPowerSocket" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_power_socket') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasPowerSocket" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasUsb" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_usb') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasUsb" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasShelf" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_shelf') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasShelf" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasHook" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_hook') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasHook" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasLocker" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_locker') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasLocker" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="lockerHasLock" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.locker_has_lock') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="lockerHasLock" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasLuggageSpace" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.has_luggage_space') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasLuggageSpace" />
                        </flux:field>
                    </div>
                    @break

                @case(3)
                    <div class="grid gap-3 sm:grid-cols-2">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="nearWindow" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.near_window') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="nearWindow" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="nearDoor" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.near_door') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="nearDoor" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="nearRadiator" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.near_radiator') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="nearRadiator" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="nearAirConditioner" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.near_air_conditioner') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="nearAirConditioner" />
                        </flux:field>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach(['privacyLevel' => 'privacy_level', 'noiseLevel' => 'noise_level'] as $property => $field)
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.'.$field) }}</span>
    </span>
</flux:label>
                                <flux:select wire:model.change="{{ $property }}">
                                    <flux:select.option value="">{{ __('host.sleeping_place_wizard.options.not_specified') }}</flux:select.option>
                                    @foreach($this->levelOptions($field) as $value => $label)
                                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="{{ $property }}" />
                            </flux:field>
                        @endforeach

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.max_guests') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuests" icon="users" />
                            <flux:error name="maxGuests" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.min_guest_age') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="minGuestAge" icon="user" />
                            <flux:error name="minGuestAge" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.max_guest_age') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuestAge" icon="user" />
                            <flux:error name="maxGuestAge" />
                        </flux:field>
                    </div>

                    <div class="grid gap-3">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="suitableForTallPerson" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.suitable_for_tall_person') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="suitableForTallPerson" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="suitableForElderly" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.suitable_for_elderly') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="suitableForElderly" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="suitableForLimitedMobility" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.suitable_for_limited_mobility') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="suitableForLimitedMobility" />
                        </flux:field>
                    </div>
                    @break

                @case(4)
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach([
                            'basePricePerNight' => 'base_price_per_night',
                            'weeklyPrice' => 'weekly_price',
                            'monthlyPrice' => 'monthly_price',
                            'weekendPrice' => 'weekend_price',
                            'holidayPrice' => 'holiday_price',
                            'cleaningFee' => 'cleaning_fee',
                            'depositAmount' => 'deposit_amount',
                        ] as $property => $field)
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.'.$field) }}</span>
    </span>
</flux:label>
                                <flux:input type="number" inputmode="decimal" step="0.01" wire:model.blur="{{ $property }}" icon="home-modern" />
                                <flux:error name="{{ $property }}" />
                            </flux:field>
                        @endforeach
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.currency') }}</span>
    </span>
</flux:label>
                            <flux:input maxlength="3" wire:model.blur="currency" icon="banknotes" />
                            <flux:error name="currency" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.min_nights') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="minNights" icon="numbered-list" />
                            <flux:error name="minNights" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.max_nights') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxNights" icon="numbered-list" />
                            <flux:error name="maxNights" />
                        </flux:field>
                    </div>

                    <div class="grid gap-3">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="instantBookingEnabled" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.instant_booking_enabled') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="instantBookingEnabled" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="requiresHostApproval" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.requires_host_approval') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="requiresHostApproval" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="extensionsAllowed" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.fields.extensions_allowed') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="extensionsAllowed" />
                        </flux:field>
                    </div>
                    @break

                @case(5)
                    <div class="grid gap-5">
                        @foreach($this->contentLocales() as $locale)
                            <div class="space-y-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <flux:heading size="sm">
                                    <span class="inline-flex min-w-0 items-center gap-2">
                                        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                        <span class="min-w-0">{{ $locale['name'] }}</span>
                                    </span>
                                </flux:heading>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.translation_fields.title', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:input wire:model.blur="translations.{{ $locale['code'] }}.title" icon="language" />
                                    <flux:error name="translations.{{ $locale['code'] }}.title" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.translation_fields.description', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="4" wire:model.blur="translations.{{ $locale['code'] }}.description" />
                                    <flux:error name="translations.{{ $locale['code'] }}.description" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.sleeping_place_wizard.translation_fields.special_conditions', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="3" wire:model.blur="translations.{{ $locale['code'] }}.special_conditions" />
                                    <flux:error name="translations.{{ $locale['code'] }}.special_conditions" />
                                </flux:field>
                            </div>
                        @endforeach
                    </div>
                    @break

                @case(6)
                    <livewire:catalog.rule-picker wire:model="ruleIds" context="sleeping_place" />
                    <flux:error name="ruleIds" />
                    @break

                @case(7)
                    <div class="grid gap-4">
                        @if($sleepingPlaceId)
                            <livewire:media.manage-media
                                owner-type="sleeping_place"
                                :owner-id="$sleepingPlaceId"
                                collection="exact_place"
                                :max-items="8"
                                :wire:key="'sleeping-place-media-'.$sleepingPlaceId"
                            />
                        @endif

                        @foreach($this->wizardPhotoFields() as $photoField)
                            <div class="space-y-2">
                                <flux:file-upload
                                    wire:model="{{ $photoField['field'] }}"
                                    :label="__('host.sleeping_place_wizard.photos.'.$photoField['slot'])"
                                    :description="__('host.sleeping_place_wizard.helpers.photo')"
                                    :error="$errors->first($photoField['field'])"
                                >
                                    <flux:file-upload.dropzone
                                        :heading="__('host.sleeping_place_wizard.photos.'.$photoField['slot'])"
                                        :text="__('host.sleeping_place_wizard.helpers.photo')"
                                        with-progress
                                        inline
                                    />
                                </flux:file-upload>
                                <flux:text wire:loading wire:target="{{ $photoField['field'] }}" size="sm" class="text-zinc-500 dark:text-zinc-400">
                                    {{ __('media.manager.uploading') }}
                                </flux:text>

                                @if($photoField['preview'])
                                    <div class="mt-2 flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-700 dark:bg-zinc-900">
                                        <img src="{{ $photoField['preview']['url'] }}" alt="{{ $photoField['preview']['caption'] }}" @if($photoField['preview']['saved']) loading="lazy" decoding="async" @endif class="size-16 shrink-0 rounded-md object-cover">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $photoField['preview']['label'] }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <flux:heading size="sm">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.sleeping_place_wizard.readiness.title') }}</span>
                                </span>
                            </flux:heading>
                            <div class="mt-3 grid gap-2">
                                @foreach($this->readinessChecklist() as $item)
                                    <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                        <span>{{ $item['label'] }}</span>
                                        @if($item['done'])
                                            <flux:badge size="sm" color="green" icon="check-circle">{{ __('host.sleeping_place_wizard.readiness.done') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" icon="check-circle">{{ __('host.sleeping_place_wizard.readiness.later') }}</flux:badge>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @break
            @endswitch
        </flux:card>

        @if($sleepingPlaceId && $step === 7)
            <livewire:host.hints.host-before-publish-checklist
                :sleeping-place-id="$sleepingPlaceId"
                :key="'host-before-publish-'.$sleepingPlaceId"
            />
        @endif

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <div class="grid grid-cols-2 gap-3">
                <flux:button type="button" variant="ghost" wire:click="previousStep" :disabled="$step === 1" icon="arrow-left">
                    {{ __('host.sleeping_place_wizard.actions.back') }}
                </flux:button>

                @if($step < 7)
                    <flux:button type="button" variant="primary" wire:click="nextStep" class="data-loading:opacity-70" icon="arrow-right">
                        <span wire:loading.remove wire:target="nextStep">{{ __('host.sleeping_place_wizard.actions.save_and_continue') }}</span>
                        <span wire:loading wire:target="nextStep">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @else
                    <flux:button type="submit" variant="primary" class="data-loading:opacity-70" icon="eye">
                        <span wire:loading.remove wire:target="publish">{{ __('host.sleeping_place_wizard.actions.review_and_save') }}</span>
                        <span wire:loading wire:target="publish">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @endif
            </div>

            <div class="mt-3">
                <flux:button class="w-full" href="{{ route('host.sleeping-places.index', ['locale' => app()->getLocale(), 'room' => $roomId]) }}" variant="ghost" wire:navigate icon="x-mark">
                    {{ __('app.actions.cancel') }}
                </flux:button>
            </div>
        </div>
    </form>
</x-ui.page>
