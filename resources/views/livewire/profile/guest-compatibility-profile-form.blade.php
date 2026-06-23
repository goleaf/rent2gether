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
            <flux:select wire:model.change="step" label="{{ __('compatibility.fields.current_step') }}">
                @foreach(['smoking_pets', 'sleep_schedule', 'work_study', 'social_quiet', 'cleanliness', 'room_people', 'sleeping_place', 'entry'] as $section)
                    <flux:select.option value="{{ $section }}">{{ __('compatibility.sections.'.$section) }}</flux:select.option>
                @endforeach
            </flux:select>

            @if($step === 'smoking_pets')
                <div class="grid gap-3">
                    <flux:checkbox wire:model.change="smokes" label="{{ __('compatibility.fields.smokes') }}" />
                    <flux:checkbox wire:model.change="travellingWithPet" label="{{ __('compatibility.fields.travelling_with_pet') }}" />
                    <flux:checkbox wire:model.change="avoidsPets" label="{{ __('compatibility.fields.avoids_pets') }}" />
                    <flux:checkbox wire:model.change="hasPetAllergy" label="{{ __('compatibility.fields.has_pet_allergy') }}" />
                </div>
            @elseif($step === 'sleep_schedule')
                <div class="grid gap-3">
                    <flux:checkbox wire:model.change="wakesUpEarly" label="{{ __('compatibility.fields.wakes_up_early') }}" />
                    <flux:checkbox wire:model.change="sleepsLate" label="{{ __('compatibility.fields.sleeps_late') }}" />
                    <flux:checkbox wire:model.change="worksAtNight" label="{{ __('compatibility.fields.works_at_night') }}" />
                    <flux:checkbox wire:model.change="needsQuietAtNight" label="{{ __('compatibility.fields.needs_quiet_at_night') }}" />
                    <flux:checkbox wire:model.change="sensitiveToLightAtNight" label="{{ __('compatibility.fields.sensitive_to_light_at_night') }}" />
                    <flux:checkbox wire:model.change="sensitiveToNoiseAtNight" label="{{ __('compatibility.fields.sensitive_to_noise_at_night') }}" />
                </div>
            @elseif($step === 'work_study')
                <div class="grid gap-3">
                    <flux:checkbox wire:model.change="student" label="{{ __('compatibility.fields.student') }}" />
                    <flux:checkbox wire:model.change="working" label="{{ __('compatibility.fields.working') }}" />
                    <flux:checkbox wire:model.change="remoteWorker" label="{{ __('compatibility.fields.remote_worker') }}" />
                    <flux:checkbox wire:model.change="needsWorkspace" label="{{ __('compatibility.fields.needs_workspace') }}" />
                    <flux:checkbox wire:model.change="needsFastWifi" label="{{ __('compatibility.fields.needs_fast_wifi') }}" />
                    <flux:checkbox wire:model.change="needsPowerSocket" label="{{ __('compatibility.fields.needs_power_socket') }}" />
                </div>
            @elseif($step === 'social_quiet')
                <div class="grid gap-3">
                    <flux:checkbox wire:model.change="prefersPrivateSpace" label="{{ __('compatibility.fields.prefers_private_space') }}" />
                    <flux:checkbox wire:model.change="comfortableWithStrangers" label="{{ __('compatibility.fields.comfortable_with_strangers') }}" />
                    <flux:select wire:model.change="socialLevel" label="{{ __('compatibility.fields.social_level') }}">
                        <flux:select.option value="">{{ __('compatibility.options.not_set') }}</flux:select.option>
                        <flux:select.option value="quiet">{{ __('compatibility.options.quiet') }}</flux:select.option>
                        <flux:select.option value="balanced">{{ __('compatibility.options.balanced') }}</flux:select.option>
                        <flux:select.option value="social">{{ __('compatibility.options.social') }}</flux:select.option>
                    </flux:select>
                </div>
            @elseif($step === 'cleanliness')
                <div class="grid gap-3">
                    <flux:select wire:model.change="cleanlinessExpectation" label="{{ __('compatibility.fields.cleanliness_expectation') }}">
                        <flux:select.option value="">{{ __('compatibility.options.not_set') }}</flux:select.option>
                        <flux:select.option value="simple">{{ __('compatibility.options.simple_cleanliness') }}</flux:select.option>
                        <flux:select.option value="normal">{{ __('compatibility.options.normal_cleanliness') }}</flux:select.option>
                        <flux:select.option value="strict">{{ __('compatibility.options.strict_cleanliness') }}</flux:select.option>
                    </flux:select>
                    <flux:checkbox wire:model.change="readyToJoinCleaning" label="{{ __('compatibility.fields.ready_to_join_cleaning') }}" />
                </div>
            @elseif($step === 'room_people')
                <div class="grid gap-3">
                    <flux:checkbox wire:model.change="wantsPrivateRoom" label="{{ __('compatibility.fields.wants_private_room') }}" />
                    <flux:checkbox wire:model.change="comfortableWithSharedRoom" label="{{ __('compatibility.fields.comfortable_with_shared_room') }}" />
                    <flux:input type="number" min="1" max="12" inputmode="numeric" wire:model.change="maxPeopleInRoom" label="{{ __('compatibility.fields.max_people_in_room') }}" icon="users" />
                    <flux:checkbox wire:model.change="comfortableWithMixedRoom" label="{{ __('compatibility.fields.comfortable_with_mixed_room') }}" />
                </div>
            @elseif($step === 'sleeping_place')
                <div class="grid gap-3">
                    <flux:checkbox wire:model.change="wantsLowerBunk" label="{{ __('compatibility.fields.wants_lower_bunk') }}" />
                    <flux:checkbox wire:model.change="avoidsUpperBunk" label="{{ __('compatibility.fields.avoids_upper_bunk') }}" />
                    <flux:checkbox wire:model.change="avoidsSofa" label="{{ __('compatibility.fields.avoids_sofa') }}" />
                    <flux:checkbox wire:model.change="avoidsFloorMattress" label="{{ __('compatibility.fields.avoids_floor_mattress') }}" />
                    <flux:checkbox wire:model.change="needsLocker" label="{{ __('compatibility.fields.needs_locker') }}" />
                    <flux:checkbox wire:model.change="needsLockerLock" label="{{ __('compatibility.fields.needs_locker_lock') }}" />
                    <flux:checkbox wire:model.change="needsBedding" label="{{ __('compatibility.fields.needs_bedding') }}" />
                    <flux:checkbox wire:model.change="needsTowel" label="{{ __('compatibility.fields.needs_towel') }}" />
                    <flux:checkbox wire:model.change="needsCurtain" label="{{ __('compatibility.fields.needs_curtain') }}" />
                </div>
            @else
                <div class="grid gap-3">
                    <flux:checkbox wire:model.change="needsLateEntry" label="{{ __('compatibility.fields.needs_late_entry') }}" />
                    <flux:checkbox wire:model.change="needsSelfCheckIn" label="{{ __('compatibility.fields.needs_self_check_in') }}" />
                    <flux:checkbox wire:model.change="needs247Access" label="{{ __('compatibility.fields.needs_24_7_access') }}" />
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
