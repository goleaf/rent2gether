<div class="space-y-4">
    @if(session('co_living_status'))
        <flux:badge color="green" icon="check-circle">{{ session('co_living_status') }}</flux:badge>
    @endif

    <form wire:submit="save" class="space-y-4">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('occupants.profile.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('occupants.profile.helper') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model.blur="publicAlias" label="{{ __('occupants.fields.public_alias') }}" :error="$errors->first('publicAlias')" icon="user" />
                <flux:select wire:model.change="ageRange" label="{{ __('occupants.fields.age_range') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="18-24">{{ __('occupants.options.age_ranges.18_24') }}</flux:select.option>
                    <flux:select.option value="25-34">{{ __('occupants.options.age_ranges.25_34') }}</flux:select.option>
                    <flux:select.option value="35-44">{{ __('occupants.options.age_ranges.35_44') }}</flux:select.option>
                    <flux:select.option value="45-54">{{ __('occupants.options.age_ranges.45_54') }}</flux:select.option>
                    <flux:select.option value="55+">{{ __('occupants.options.age_ranges.55_plus') }}</flux:select.option>
                </flux:select>
                <flux:input wire:model.blur="languages" label="{{ __('occupants.fields.languages') }}" placeholder="{{ __('occupants.placeholders.languages') }}" icon="language" />
                <flux:select wire:model.change="genderForRoomPolicy" label="{{ __('occupants.fields.gender_for_room_policy') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="female">{{ __('occupants.options.gender.female') }}</flux:select.option>
                    <flux:select.option value="male">{{ __('occupants.options.gender.male') }}</flux:select.option>
                    <flux:select.option value="not_specified">{{ __('occupants.options.gender.not_specified') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model.change="stayPurpose" label="{{ __('occupants.fields.stay_purpose') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="tourism">{{ __('occupants.options.stay_purpose.tourism') }}</flux:select.option>
                    <flux:select.option value="work">{{ __('occupants.options.stay_purpose.work') }}</flux:select.option>
                    <flux:select.option value="study">{{ __('occupants.options.stay_purpose.study') }}</flux:select.option>
                    <flux:select.option value="temporary_housing">{{ __('occupants.options.stay_purpose.temporary_housing') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model.change="guestType" label="{{ __('occupants.fields.guest_type') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="tourist">{{ __('occupants.tourist') }}</flux:select.option>
                    <flux:select.option value="student">{{ __('occupants.student') }}</flux:select.option>
                    <flux:select.option value="working">{{ __('occupants.working') }}</flux:select.option>
                    <flux:select.option value="remote_worker">{{ __('occupants.remote_worker') }}</flux:select.option>
                    <flux:select.option value="long_term_guest">{{ __('occupants.long_term_guest') }}</flux:select.option>
                    <flux:select.option value="short_term_guest">{{ __('occupants.short_term_guest') }}</flux:select.option>
                </flux:select>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('occupants.profile.lifestyle') }}</span>
                </span>
            </flux:heading>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model.change="sleepSchedule" label="{{ __('occupants.fields.sleep_schedule') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="early_bird">{{ __('occupants.early_bird') }}</flux:select.option>
                    <flux:select.option value="normal">{{ __('occupants.options.schedule.normal') }}</flux:select.option>
                    <flux:select.option value="night_owl">{{ __('occupants.night_owl') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model.change="homePresenceLevel" label="{{ __('occupants.fields.home_presence_level') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="often_home">{{ __('occupants.often_home') }}</flux:select.option>
                    <flux:select.option value="balanced">{{ __('occupants.options.presence.balanced') }}</flux:select.option>
                    <flux:select.option value="rarely_home">{{ __('occupants.rarely_home') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model.change="socialLevel" label="{{ __('occupants.fields.social_level') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="quiet">{{ __('occupants.quiet') }}</flux:select.option>
                    <flux:select.option value="calm">{{ __('occupants.options.social.calm') }}</flux:select.option>
                    <flux:select.option value="social">{{ __('occupants.social') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model.change="cleanlinessLevel" label="{{ __('occupants.fields.cleanliness_level') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="basic">{{ __('occupants.options.cleanliness.basic') }}</flux:select.option>
                    <flux:select.option value="tidy">{{ __('occupants.options.cleanliness.tidy') }}</flux:select.option>
                    <flux:select.option value="very_tidy">{{ __('occupants.options.cleanliness.very_tidy') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="tourist" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.tourist') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="tourist" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="student" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.student') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="student" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="working" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.working') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="working" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="remoteWorker" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.remote_worker') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="remoteWorker" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="longTermGuest" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.long_term_guest') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="longTermGuest" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="shortTermGuest" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.short_term_guest') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="shortTermGuest" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="smokes" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.smokes') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="smokes" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hasPet" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.fields.has_pet') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasPet" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="prefersQuiet" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.quiet') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="prefersQuiet" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="participatesInCleaning" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.fields.participates_in_cleaning') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="participatesInCleaning" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="respectsPersonalSpace" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.fields.respects_personal_space') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="respectsPersonalSpace" />
                </flux:field>
            </div>
        </flux:card>

        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" icon="check">
            <span wire:loading.remove>{{ __('occupants.actions.save_profile') }}</span>
            <span wire:loading>{{ __('occupants.actions.saving') }}</span>
        </flux:button>
    </form>
</div>
