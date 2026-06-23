<form wire:submit="save" class="space-y-5">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="scale" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('compatibility.profile_title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.profile_helper') }}</flux:text>
    </div>

    @if(session('compatibility-status'))
        <flux:callout color="green" icon="check-circle">
            {{ session('compatibility-status') }}
        </flux:callout>
    @endif

    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-4">
                        <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.fields.current_step') }}</span>
                    </span>
                </flux:label>
                <flux:select wire:model.change="step">
                @foreach(['smoking_pets', 'sleep_schedule', 'work_study', 'social_quiet', 'cleanliness', 'room_people', 'sleeping_place', 'entry'] as $section)
                    <flux:select.option value="{{ $section }}">{{ __('compatibility.sections.'.$section) }}</flux:select.option>
                @endforeach
            </flux:select>
                <flux:error name="step" />
            </flux:field>

            @if($step === 'smoking_pets')
                <div class="grid gap-3">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="smokes" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.smokes') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="smokes" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="travellingWithPet" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.travelling_with_pet') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="travellingWithPet" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="avoidsPets" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.avoids_pets') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="avoidsPets" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="hasPetAllergy" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.has_pet_allergy') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="hasPetAllergy" />
                    </flux:field>
                </div>
            @elseif($step === 'sleep_schedule')
                <div class="grid gap-3">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="wakesUpEarly" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.wakes_up_early') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="wakesUpEarly" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="sleepsLate" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.sleeps_late') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="sleepsLate" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="worksAtNight" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.works_at_night') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="worksAtNight" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsQuietAtNight" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_quiet_at_night') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsQuietAtNight" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="sensitiveToLightAtNight" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.sensitive_to_light_at_night') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="sensitiveToLightAtNight" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="sensitiveToNoiseAtNight" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.sensitive_to_noise_at_night') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="sensitiveToNoiseAtNight" />
                    </flux:field>
                </div>
            @elseif($step === 'work_study')
                <div class="grid gap-3">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="student" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.student') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="student" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="working" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.working') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="working" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="remoteWorker" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.remote_worker') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="remoteWorker" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsWorkspace" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_workspace') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsWorkspace" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsFastWifi" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_fast_wifi') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsFastWifi" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsPowerSocket" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_power_socket') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsPowerSocket" />
                    </flux:field>
                </div>
            @elseif($step === 'social_quiet')
                <div class="grid gap-3">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="prefersPrivateSpace" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.prefers_private_space') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="prefersPrivateSpace" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="comfortableWithStrangers" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.comfortable_with_strangers') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="comfortableWithStrangers" />
                    </flux:field>
                                        <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.social_level') }}</span>
                            </span>
                        </flux:label>
                        <flux:select wire:model.change="socialLevel">
                        <flux:select.option value="">{{ __('compatibility.options.not_set') }}</flux:select.option>
                        <flux:select.option value="quiet">{{ __('compatibility.options.quiet') }}</flux:select.option>
                        <flux:select.option value="balanced">{{ __('compatibility.options.balanced') }}</flux:select.option>
                        <flux:select.option value="social">{{ __('compatibility.options.social') }}</flux:select.option>
                    </flux:select>
                        <flux:error name="socialLevel" />
                    </flux:field>
                </div>
            @elseif($step === 'cleanliness')
                <div class="grid gap-3">
                                        <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.cleanliness_expectation') }}</span>
                            </span>
                        </flux:label>
                        <flux:select wire:model.change="cleanlinessExpectation">
                        <flux:select.option value="">{{ __('compatibility.options.not_set') }}</flux:select.option>
                        <flux:select.option value="simple">{{ __('compatibility.options.simple_cleanliness') }}</flux:select.option>
                        <flux:select.option value="normal">{{ __('compatibility.options.normal_cleanliness') }}</flux:select.option>
                        <flux:select.option value="strict">{{ __('compatibility.options.strict_cleanliness') }}</flux:select.option>
                    </flux:select>
                        <flux:error name="cleanlinessExpectation" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="readyToJoinCleaning" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.ready_to_join_cleaning') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="readyToJoinCleaning" />
                    </flux:field>
                </div>
            @elseif($step === 'room_people')
                <div class="grid gap-3">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="wantsPrivateRoom" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.wants_private_room') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="wantsPrivateRoom" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="comfortableWithSharedRoom" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.comfortable_with_shared_room') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="comfortableWithSharedRoom" />
                    </flux:field>
                                        <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.max_people_in_room') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="number" min="1" max="12" inputmode="numeric" wire:model.change="maxPeopleInRoom" icon="users" />
                        <flux:error name="maxPeopleInRoom" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="comfortableWithMixedRoom" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.comfortable_with_mixed_room') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="comfortableWithMixedRoom" />
                    </flux:field>
                </div>
            @elseif($step === 'sleeping_place')
                <div class="grid gap-3">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="wantsLowerBunk" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.wants_lower_bunk') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="wantsLowerBunk" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="avoidsUpperBunk" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.avoids_upper_bunk') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="avoidsUpperBunk" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="avoidsSofa" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.avoids_sofa') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="avoidsSofa" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="avoidsFloorMattress" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.avoids_floor_mattress') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="avoidsFloorMattress" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsLocker" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_locker') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsLocker" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsLockerLock" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_locker_lock') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsLockerLock" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsBedding" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_bedding') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsBedding" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsTowel" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_towel') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsTowel" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsCurtain" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_curtain') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsCurtain" />
                    </flux:field>
                </div>
            @else
                <div class="grid gap-3">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsLateEntry" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_late_entry') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsLateEntry" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsSelfCheckIn" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_self_check_in') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsSelfCheckIn" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needs247Access" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.fields.needs_24_7_access') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needs247Access" />
                    </flux:field>
                </div>
            @endif
        </div>
    </div>

    <div class="sticky bottom-0 -mx-1 bg-white/95 p-1 backdrop-blur dark:bg-zinc-950/95">
        <flux:button type="submit" variant="primary" class="w-full" icon="check" wire:loading.attr="disabled">
            {{ __('compatibility.actions.save_profile') }}
        </flux:button>
    </div>
</form>
