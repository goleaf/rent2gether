<div class="mx-auto max-w-3xl space-y-5">
    <section class="space-y-2">
        <flux:badge color="emerald">{{ __('host.sleeping_place_wizard.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">{{ $sleepingPlaceId ? __('host.sleeping_place_wizard.edit_heading') : __('host.sleeping_place_wizard.heading') }}</flux:heading>
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
            <flux:badge size="sm">{{ $status ? __('statuses.sleeping_place.'.$status) : __('statuses.sleeping_place.draft') }}</flux:badge>
        </div>

        <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ (int) (($step / 7) * 100) }}%"></div>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach($this->wizardSteps() as $wizardStep)
                <button
                    type="button"
                    wire:click="$set('step', {{ $wizardStep['number'] }})"
                    class="shrink-0 rounded-full border px-3 py-1.5 text-xs {{ $step === $wizardStep['number'] ? 'border-emerald-500 bg-emerald-50 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200' : 'border-zinc-200 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400' }}"
                    title="{{ $wizardStep['title'] }}"
                >
                    {{ $wizardStep['number'] }}
                </button>
            @endforeach
        </div>
    </flux:card>

    @if($sleepingPlaceId)
        @php
            $hostHintStep = match ($step) {
                4 => 'pricing',
                5 => 'description',
                6 => 'rules',
                7 => 'photos',
                default => 'overview',
            };
        @endphp

        <livewire:host.hints.host-wizard-hints
            target-type="sleeping_place"
            :target-id="$sleepingPlaceId"
            :step="$hostHintStep"
            :key="'host-wizard-hints-'.$sleepingPlaceId.'-'.$step"
        />
    @endif

    <form wire:submit="publish" class="space-y-5">
        <flux:card class="space-y-5">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('host.sleeping_place_wizard.steps.'.$step.'.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('host.sleeping_place_wizard.steps.'.$step.'.helper') }}
                </flux:text>
            </div>

            @switch($step)
                @case(1)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.place_number') }}</flux:label>
                            <flux:input wire:model.blur="placeNumber" />
                            <flux:error name="placeNumber" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.display_name') }}</flux:label>
                            <flux:input wire:model.blur="displayName" />
                            <flux:error name="displayName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.type') }}</flux:label>
                            <flux:select wire:model.change="type">
                                @foreach($this->typeOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="type" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.status') }}</flux:label>
                            <flux:select wire:model.change="status">
                                @foreach($this->statusOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="status" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.bunk_level') }}</flux:label>
                            <flux:input wire:model.blur="bunkLevel" />
                            <flux:error name="bunkLevel" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.length_cm') }}</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="lengthCm" />
                            <flux:error name="lengthCm" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.width_cm') }}</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="widthCm" />
                            <flux:error name="widthCm" />
                        </flux:field>
                    </div>
                    @break

                @case(2)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.mattress_type') }}</flux:label>
                            <flux:input wire:model.blur="mattressType" />
                            <flux:error name="mattressType" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.mattress_firmness') }}</flux:label>
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
                        <flux:checkbox wire:model.change="hasPillow" label="{{ __('host.sleeping_place_wizard.fields.has_pillow') }}" />
                        <flux:checkbox wire:model.change="hasBlanket" label="{{ __('host.sleeping_place_wizard.fields.has_blanket') }}" />
                        <flux:checkbox wire:model.change="hasBedding" label="{{ __('host.sleeping_place_wizard.fields.has_bedding') }}" />
                        <flux:checkbox wire:model.change="hasTowel" label="{{ __('host.sleeping_place_wizard.fields.has_towel') }}" />
                        <flux:checkbox wire:model.change="hasCurtain" label="{{ __('host.sleeping_place_wizard.fields.has_curtain') }}" />
                        <flux:checkbox wire:model.change="hasLamp" label="{{ __('host.sleeping_place_wizard.fields.has_lamp') }}" />
                        <flux:checkbox wire:model.change="hasPowerSocket" label="{{ __('host.sleeping_place_wizard.fields.has_power_socket') }}" />
                        <flux:checkbox wire:model.change="hasUsb" label="{{ __('host.sleeping_place_wizard.fields.has_usb') }}" />
                        <flux:checkbox wire:model.change="hasShelf" label="{{ __('host.sleeping_place_wizard.fields.has_shelf') }}" />
                        <flux:checkbox wire:model.change="hasHook" label="{{ __('host.sleeping_place_wizard.fields.has_hook') }}" />
                        <flux:checkbox wire:model.change="hasLocker" label="{{ __('host.sleeping_place_wizard.fields.has_locker') }}" />
                        <flux:checkbox wire:model.change="lockerHasLock" label="{{ __('host.sleeping_place_wizard.fields.locker_has_lock') }}" />
                        <flux:checkbox wire:model.change="hasLuggageSpace" label="{{ __('host.sleeping_place_wizard.fields.has_luggage_space') }}" />
                    </div>
                    @break

                @case(3)
                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:checkbox wire:model.change="nearWindow" label="{{ __('host.sleeping_place_wizard.fields.near_window') }}" />
                        <flux:checkbox wire:model.change="nearDoor" label="{{ __('host.sleeping_place_wizard.fields.near_door') }}" />
                        <flux:checkbox wire:model.change="nearRadiator" label="{{ __('host.sleeping_place_wizard.fields.near_radiator') }}" />
                        <flux:checkbox wire:model.change="nearAirConditioner" label="{{ __('host.sleeping_place_wizard.fields.near_air_conditioner') }}" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach(['privacyLevel' => 'privacy_level', 'noiseLevel' => 'noise_level'] as $property => $field)
                            <flux:field>
                                <flux:label>{{ __('host.sleeping_place_wizard.fields.'.$field) }}</flux:label>
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
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.max_guests') }}</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuests" />
                            <flux:error name="maxGuests" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.min_guest_age') }}</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="minGuestAge" />
                            <flux:error name="minGuestAge" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.max_guest_age') }}</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuestAge" />
                            <flux:error name="maxGuestAge" />
                        </flux:field>
                    </div>

                    <div class="grid gap-3">
                        <flux:checkbox wire:model.change="suitableForTallPerson" label="{{ __('host.sleeping_place_wizard.fields.suitable_for_tall_person') }}" />
                        <flux:checkbox wire:model.change="suitableForElderly" label="{{ __('host.sleeping_place_wizard.fields.suitable_for_elderly') }}" />
                        <flux:checkbox wire:model.change="suitableForLimitedMobility" label="{{ __('host.sleeping_place_wizard.fields.suitable_for_limited_mobility') }}" />
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
                                <flux:label>{{ __('host.sleeping_place_wizard.fields.'.$field) }}</flux:label>
                                <flux:input type="number" inputmode="decimal" step="0.01" wire:model.blur="{{ $property }}" />
                                <flux:error name="{{ $property }}" />
                            </flux:field>
                        @endforeach
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.currency') }}</flux:label>
                            <flux:input maxlength="3" wire:model.blur="currency" />
                            <flux:error name="currency" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.min_nights') }}</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="minNights" />
                            <flux:error name="minNights" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('host.sleeping_place_wizard.fields.max_nights') }}</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxNights" />
                            <flux:error name="maxNights" />
                        </flux:field>
                    </div>

                    <div class="grid gap-3">
                        <flux:checkbox wire:model.change="instantBookingEnabled" label="{{ __('host.sleeping_place_wizard.fields.instant_booking_enabled') }}" />
                        <flux:checkbox wire:model.change="requiresHostApproval" label="{{ __('host.sleeping_place_wizard.fields.requires_host_approval') }}" />
                        <flux:checkbox wire:model.change="extensionsAllowed" label="{{ __('host.sleeping_place_wizard.fields.extensions_allowed') }}" />
                    </div>
                    @break

                @case(5)
                    <div class="grid gap-5">
                        @foreach(['en' => 'En', 'ru' => 'Ru'] as $locale => $suffix)
                            <div class="space-y-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <flux:heading size="sm">{{ __('host.sleeping_place_wizard.locales.'.$locale) }}</flux:heading>
                                <flux:field>
                                    <flux:label>{{ __('host.sleeping_place_wizard.fields.title_'.$locale) }}</flux:label>
                                    <flux:input wire:model.blur="title{{ $suffix }}" />
                                    <flux:error name="title{{ $suffix }}" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('host.sleeping_place_wizard.fields.description_'.$locale) }}</flux:label>
                                    <flux:textarea rows="4" wire:model.blur="description{{ $suffix }}" />
                                    <flux:error name="description{{ $suffix }}" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('host.sleeping_place_wizard.fields.special_conditions_'.$locale) }}</flux:label>
                                    <flux:textarea rows="3" wire:model.blur="specialConditions{{ $suffix }}" />
                                    <flux:error name="specialConditions{{ $suffix }}" />
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

                        @foreach(['exactPhoto' => 'exact_place', 'detailPhoto' => 'detail'] as $field => $slot)
                            <flux:field>
                                <flux:label>{{ __('host.sleeping_place_wizard.photos.'.$slot) }}</flux:label>
                                <flux:input type="file" accept="image/*" wire:model="{{ $field }}" />
                                <flux:description>{{ __('host.sleeping_place_wizard.helpers.photo') }}</flux:description>
                                <flux:error name="{{ $field }}" />
                            </flux:field>
                        @endforeach

                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <flux:heading size="sm">{{ __('host.sleeping_place_wizard.readiness.title') }}</flux:heading>
                            <div class="mt-3 grid gap-2">
                                @foreach($this->readinessChecklist() as $item)
                                    <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                        <span>{{ $item['label'] }}</span>
                                        @if($item['done'])
                                            <flux:badge size="sm" color="green">{{ __('host.sleeping_place_wizard.readiness.done') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm">{{ __('host.sleeping_place_wizard.readiness.later') }}</flux:badge>
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
                <flux:button type="button" variant="ghost" wire:click="previousStep" :disabled="$step === 1">
                    {{ __('host.sleeping_place_wizard.actions.back') }}
                </flux:button>

                @if($step < 7)
                    <flux:button type="button" variant="primary" wire:click="nextStep" class="data-loading:opacity-70">
                        <span wire:loading.remove wire:target="nextStep">{{ __('host.sleeping_place_wizard.actions.save_and_continue') }}</span>
                        <span wire:loading wire:target="nextStep">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @else
                    <flux:button type="submit" variant="primary" class="data-loading:opacity-70">
                        <span wire:loading.remove wire:target="publish">{{ __('host.sleeping_place_wizard.actions.review_and_save') }}</span>
                        <span wire:loading wire:target="publish">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @endif
            </div>

            <div class="mt-3">
                <flux:button class="w-full" href="{{ route('host.sleeping-places.index', ['locale' => app()->getLocale(), 'room' => $roomId]) }}" variant="ghost" wire:navigate>
                    {{ __('app.actions.cancel') }}
                </flux:button>
            </div>
        </div>
    </form>
</div>
