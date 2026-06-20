<div class="space-y-4">
    @if(session('co_living_status'))
        <flux:badge color="green">{{ session('co_living_status') }}</flux:badge>
    @endif

    <form wire:submit="save" class="space-y-4">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('occupants.profile.title') }}</flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('occupants.profile.helper') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model.blur="publicAlias" label="{{ __('occupants.fields.public_alias') }}" :error="$errors->first('publicAlias')" />
                <flux:select wire:model.change="ageRange" label="{{ __('occupants.fields.age_range') }}">
                    <option value="">{{ __('occupants.options.not_set') }}</option>
                    <option value="18-24">{{ __('occupants.options.age_ranges.18_24') }}</option>
                    <option value="25-34">{{ __('occupants.options.age_ranges.25_34') }}</option>
                    <option value="35-44">{{ __('occupants.options.age_ranges.35_44') }}</option>
                    <option value="45-54">{{ __('occupants.options.age_ranges.45_54') }}</option>
                    <option value="55+">{{ __('occupants.options.age_ranges.55_plus') }}</option>
                </flux:select>
                <flux:input wire:model.blur="languages" label="{{ __('occupants.fields.languages') }}" placeholder="{{ __('occupants.placeholders.languages') }}" />
                <flux:select wire:model.change="genderForRoomPolicy" label="{{ __('occupants.fields.gender_for_room_policy') }}">
                    <option value="">{{ __('occupants.options.not_set') }}</option>
                    <option value="female">{{ __('occupants.options.gender.female') }}</option>
                    <option value="male">{{ __('occupants.options.gender.male') }}</option>
                    <option value="not_specified">{{ __('occupants.options.gender.not_specified') }}</option>
                </flux:select>
                <flux:select wire:model.change="stayPurpose" label="{{ __('occupants.fields.stay_purpose') }}">
                    <option value="">{{ __('occupants.options.not_set') }}</option>
                    <option value="tourism">{{ __('occupants.options.stay_purpose.tourism') }}</option>
                    <option value="work">{{ __('occupants.options.stay_purpose.work') }}</option>
                    <option value="study">{{ __('occupants.options.stay_purpose.study') }}</option>
                    <option value="temporary_housing">{{ __('occupants.options.stay_purpose.temporary_housing') }}</option>
                </flux:select>
                <flux:select wire:model.change="guestType" label="{{ __('occupants.fields.guest_type') }}">
                    <option value="">{{ __('occupants.options.not_set') }}</option>
                    <option value="tourist">{{ __('occupants.tourist') }}</option>
                    <option value="student">{{ __('occupants.student') }}</option>
                    <option value="working">{{ __('occupants.working') }}</option>
                    <option value="remote_worker">{{ __('occupants.remote_worker') }}</option>
                    <option value="long_term_guest">{{ __('occupants.long_term_guest') }}</option>
                    <option value="short_term_guest">{{ __('occupants.short_term_guest') }}</option>
                </flux:select>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('occupants.profile.lifestyle') }}</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model.change="sleepSchedule" label="{{ __('occupants.fields.sleep_schedule') }}">
                    <option value="">{{ __('occupants.options.not_set') }}</option>
                    <option value="early_bird">{{ __('occupants.early_bird') }}</option>
                    <option value="normal">{{ __('occupants.options.schedule.normal') }}</option>
                    <option value="night_owl">{{ __('occupants.night_owl') }}</option>
                </flux:select>
                <flux:select wire:model.change="homePresenceLevel" label="{{ __('occupants.fields.home_presence_level') }}">
                    <option value="">{{ __('occupants.options.not_set') }}</option>
                    <option value="often_home">{{ __('occupants.often_home') }}</option>
                    <option value="balanced">{{ __('occupants.options.presence.balanced') }}</option>
                    <option value="rarely_home">{{ __('occupants.rarely_home') }}</option>
                </flux:select>
                <flux:select wire:model.change="socialLevel" label="{{ __('occupants.fields.social_level') }}">
                    <option value="">{{ __('occupants.options.not_set') }}</option>
                    <option value="quiet">{{ __('occupants.quiet') }}</option>
                    <option value="calm">{{ __('occupants.options.social.calm') }}</option>
                    <option value="social">{{ __('occupants.social') }}</option>
                </flux:select>
                <flux:select wire:model.change="cleanlinessLevel" label="{{ __('occupants.fields.cleanliness_level') }}">
                    <option value="">{{ __('occupants.options.not_set') }}</option>
                    <option value="basic">{{ __('occupants.options.cleanliness.basic') }}</option>
                    <option value="tidy">{{ __('occupants.options.cleanliness.tidy') }}</option>
                    <option value="very_tidy">{{ __('occupants.options.cleanliness.very_tidy') }}</option>
                </flux:select>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <flux:checkbox wire:model.change="tourist" label="{{ __('occupants.tourist') }}" />
                <flux:checkbox wire:model.change="student" label="{{ __('occupants.student') }}" />
                <flux:checkbox wire:model.change="working" label="{{ __('occupants.working') }}" />
                <flux:checkbox wire:model.change="remoteWorker" label="{{ __('occupants.remote_worker') }}" />
                <flux:checkbox wire:model.change="longTermGuest" label="{{ __('occupants.long_term_guest') }}" />
                <flux:checkbox wire:model.change="shortTermGuest" label="{{ __('occupants.short_term_guest') }}" />
                <flux:checkbox wire:model.change="smokes" label="{{ __('occupants.smokes') }}" />
                <flux:checkbox wire:model.change="hasPet" label="{{ __('occupants.fields.has_pet') }}" />
                <flux:checkbox wire:model.change="prefersQuiet" label="{{ __('occupants.quiet') }}" />
                <flux:checkbox wire:model.change="participatesInCleaning" label="{{ __('occupants.fields.participates_in_cleaning') }}" />
                <flux:checkbox wire:model.change="respectsPersonalSpace" label="{{ __('occupants.fields.respects_personal_space') }}" />
            </div>
        </flux:card>

        <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('occupants.actions.save_profile') }}</span>
            <span wire:loading>{{ __('occupants.actions.saving') }}</span>
        </flux:button>
    </form>
</div>
