<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('host.room_wizard.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ $roomId ? __('host.room_wizard.edit_heading') : __('host.room_wizard.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            {{ __('host.room_wizard.helper', ['property' => $this->property->title]) }}
        </flux:text>
    </section>

    @if($wasSaved)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ __('host.room_wizard.saved_notice') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm" class="font-medium text-zinc-700 dark:text-zinc-200">
                {{ __('host.room_wizard.progress', ['current' => $step, 'total' => 6]) }}
            </flux:text>
            <flux:badge size="sm" icon="clock">{{ $status ? __('statuses.room.'.$status) : __('statuses.room.draft') }}</flux:badge>
        </div>

        <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ (int) (($step / 6) * 100) }}%"></div>
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

    <form wire:submit="publish" class="space-y-5">
        <flux:card class="space-y-5">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.room_wizard.steps.'.$step.'.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('host.room_wizard.steps.'.$step.'.helper') }}
                </flux:text>
            </div>

            @switch($step)
                @case(1)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.room_number') }}</span>
    </span>
</flux:label>
                            <flux:input wire:model.blur="roomNumber" icon="home-modern" />
                            <flux:error name="roomNumber" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.title') }}</span>
    </span>
</flux:label>
                            <flux:input wire:model.blur="title" icon="tag" />
                            <flux:error name="title" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.room_type') }}</span>
    </span>
</flux:label>
                            <flux:select wire:model.change="roomType">
                                @foreach($this->roomTypeOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="roomType" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.gender_policy') }}</span>
    </span>
</flux:label>
                            <flux:select wire:model.change="genderPolicy">
                                @foreach($this->genderPolicyOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="genderPolicy" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.status') }}</span>
    </span>
</flux:label>
                            <flux:select wire:model.change="status">
                                @foreach($this->statusOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="status" />
                        </flux:field>
                    </div>

                    <div class="grid gap-3">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="isPrivate" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.is_private') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="isPrivate" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="isPassThrough" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.is_pass_through') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="isPassThrough" />
                        </flux:field>
                    </div>
                    @break

                @case(2)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.area') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="decimal" step="0.1" wire:model.blur="area" icon="home-modern" />
                            <flux:error name="area" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.floor') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="floor" icon="home-modern" />
                            <flux:error name="floor" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.windows_count') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="windowsCount" icon="home-modern" />
                            <flux:error name="windowsCount" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.window_view') }}</span>
    </span>
</flux:label>
                            <flux:input wire:model.blur="windowView" icon="home-modern" />
                            <flux:error name="windowView" />
                        </flux:field>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasLock" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_lock') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasLock" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasWindow" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_window') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasWindow" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasWardrobe" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_wardrobe') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasWardrobe" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasDesk" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_desk') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasDesk" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasChair" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_chair') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasChair" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasMirror" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_mirror') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasMirror" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasHeating" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_heating') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasHeating" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasAirConditioning" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_air_conditioning') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasAirConditioning" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasBalcony" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_balcony') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasBalcony" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasCurtains" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_curtains') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasCurtains" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="hasBlackoutCurtains" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.has_blackout_curtains') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="hasBlackoutCurtains" />
                        </flux:field>
                    </div>
                    @break

                @case(3)
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach(['noiseLevel' => 'noise_level', 'lightLevel' => 'light_level', 'ventilationLevel' => 'ventilation_level'] as $property => $field)
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.'.$field) }}</span>
    </span>
</flux:label>
                                <flux:select wire:model.change="{{ $property }}">
                                    <flux:select.option value="">{{ __('host.room_wizard.options.not_specified') }}</flux:select.option>
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
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.max_guests') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuests" icon="users" />
                            <flux:error name="maxGuests" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.beds_count') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="bedsCount" icon="users" />
                            <flux:error name="bedsCount" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.min_guest_age') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="minGuestAge" icon="user" />
                            <flux:error name="minGuestAge" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.max_guest_age') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuestAge" icon="user" />
                            <flux:error name="maxGuestAge" />
                        </flux:field>
                    </div>

                    <div class="grid gap-3">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="canEat" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.can_eat') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="canEat" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="canWorkAtNight" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.can_work_at_night') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="canWorkAtNight" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="canUseLightAtNight" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.can_use_light_at_night') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="canUseLightAtNight" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="canTalkAtNight" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.can_talk_at_night') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="canTalkAtNight" />
                        </flux:field>
                    </div>

                    @if((int) $bedsCount > 0)
                        <flux:callout icon="information-circle">
                            <flux:callout.text>{{ __('host.room_wizard.generate_offer', ['count' => (int) $bedsCount]) }}</flux:callout.text>
                        </flux:callout>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="generateSleepingPlacesAfterSave" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.room_wizard.fields.generate_sleeping_places') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="generateSleepingPlacesAfterSave" />
                        </flux:field>
                    @endif
                    @break

                @case(4)
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
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.translation_fields.title', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:input wire:model.blur="translations.{{ $locale['code'] }}.title" icon="language" />
                                    <flux:error name="translations.{{ $locale['code'] }}.title" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.translation_fields.description', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="4" wire:model.blur="translations.{{ $locale['code'] }}.description" />
                                    <flux:error name="translations.{{ $locale['code'] }}.description" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.translation_fields.notes', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="3" wire:model.blur="translations.{{ $locale['code'] }}.notes" />
                                    <flux:error name="translations.{{ $locale['code'] }}.notes" />
                                </flux:field>
                            </div>
                        @endforeach
                    </div>
                    @break

                @case(5)
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.room_wizard.fields.room_rules_text') }}</span>
    </span>
</flux:label>
                            <flux:textarea rows="3" wire:model.blur="roomRulesText" />
                            <flux:error name="roomRulesText" />
                        </flux:field>

                        <livewire:catalog.rule-picker wire:model="ruleIds" context="room" />
                        <flux:error name="ruleIds" />
                    </div>
                    @break

                @case(6)
                    <div class="grid gap-4">
                        @if($roomId)
                            <livewire:media.manage-media
                                owner-type="room"
                                :owner-id="$roomId"
                                collection="gallery"
                                :max-items="12"
                                :wire:key="'room-media-'.$roomId"
                            />
                        @endif

                        @foreach($this->wizardPhotoFields() as $photoField)
                            <div class="space-y-2">
                                <flux:file-upload
                                    wire:model="{{ $photoField['field'] }}"
                                    :label="__('host.room_wizard.photos.'.$photoField['slot'])"
                                    :description="__('host.room_wizard.helpers.photo')"
                                    :error="$errors->first($photoField['field'])"
                                >
                                    <flux:file-upload.dropzone
                                        :heading="__('host.room_wizard.photos.'.$photoField['slot'])"
                                        :text="__('host.room_wizard.helpers.photo')"
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
                                    <span class="min-w-0">{{ __('host.room_wizard.readiness.title') }}</span>
                                </span>
                            </flux:heading>
                            <div class="mt-3 grid gap-2">
                                @foreach($this->readinessChecklist() as $item)
                                    <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                        <span>{{ $item['label'] }}</span>
                                        <flux:badge size="sm" color="{{ $item['done'] ? 'green' : 'zinc' }}" icon="check-circle">
                                            {{ $item['done'] ? __('host.room_wizard.readiness.done') : __('host.room_wizard.readiness.later') }}
                                        </flux:badge>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @break
            @endswitch
        </flux:card>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <div class="grid grid-cols-2 gap-3">
                <flux:button type="button" variant="ghost" wire:click="previousStep" :disabled="$step === 1" icon="arrow-left">
                    {{ __('host.room_wizard.actions.back') }}
                </flux:button>

                @if($step < 6)
                    <flux:button type="button" variant="primary" wire:click="nextStep" class="data-loading:opacity-70" icon="arrow-right">
                        <span wire:loading.remove wire:target="nextStep">{{ __('host.room_wizard.actions.save_and_continue') }}</span>
                        <span wire:loading wire:target="nextStep">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @else
                    <flux:button type="submit" variant="primary" class="data-loading:opacity-70" icon="eye">
                        <span wire:loading.remove wire:target="publish">{{ __('host.room_wizard.actions.review_and_save') }}</span>
                        <span wire:loading wire:target="publish">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @endif
            </div>

            <div class="mt-3">
                <flux:button class="w-full" href="{{ route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $propertyId]) }}" variant="ghost" wire:navigate icon="x-mark">
                    {{ __('app.actions.cancel') }}
                </flux:button>
            </div>
        </div>
    </form>
</x-ui.page>
