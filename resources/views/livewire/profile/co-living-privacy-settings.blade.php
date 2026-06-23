<div class="space-y-4">
    @if(session('co_living_status'))
        <flux:badge color="green" icon="check-circle">{{ session('co_living_status') }}</flux:badge>
    @endif

    <form wire:submit="save" class="space-y-4">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="shield-check" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('occupants.privacy.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('occupants.privacy.helper') }}</flux:text>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <flux:checkbox wire:model.change="showPublicAlias" label="{{ __('occupants.privacy.show_public_alias') }}" />
                <flux:checkbox wire:model.change="showRealFirstName" label="{{ __('occupants.privacy.show_real_first_name') }}" />
                <flux:checkbox wire:model.change="showAvatar" label="{{ __('occupants.privacy.show_avatar') }}" />
                <flux:checkbox wire:model.change="showAgeRange" label="{{ __('occupants.privacy.show_age_range') }}" />
                <flux:checkbox wire:model.change="showGenderIfRoomPolicy" label="{{ __('occupants.privacy.show_gender_if_room_policy') }}" />
                <flux:checkbox wire:model.change="showLanguages" label="{{ __('occupants.privacy.show_languages') }}" />
                <flux:checkbox wire:model.change="showStayPurpose" label="{{ __('occupants.privacy.show_stay_purpose') }}" />
                <flux:checkbox wire:model.change="showGuestType" label="{{ __('occupants.privacy.show_guest_type') }}" />
                <flux:checkbox wire:model.change="showSleepSchedule" label="{{ __('occupants.privacy.show_sleep_schedule') }}" />
                <flux:checkbox wire:model.change="showHomePresence" label="{{ __('occupants.privacy.show_home_presence') }}" />
                <flux:checkbox wire:model.change="showSmokingStatus" label="{{ __('occupants.privacy.show_smoking_status') }}" />
                <flux:checkbox wire:model.change="showPetStatus" label="{{ __('occupants.privacy.show_pet_status') }}" />
                <flux:checkbox wire:model.change="showSocialLevel" label="{{ __('occupants.privacy.show_social_level') }}" />
                <flux:checkbox wire:model.change="showQuietPreference" label="{{ __('occupants.privacy.show_quiet_preference') }}" />
                <flux:checkbox wire:model.change="showRoommateRating" label="{{ __('occupants.privacy.show_roommate_rating') }}" />
                <flux:checkbox wire:model.change="showCheckoutDateToFutureRoommates" label="{{ __('occupants.privacy.show_checkout_date_to_future_roommates') }}" />
                <flux:checkbox wire:model.change="allowProfileInPrebookingSummary" label="{{ __('occupants.privacy.allow_profile_in_prebooking_summary') }}" />
                <flux:checkbox wire:model.change="allowProfileAfterConfirmedBooking" label="{{ __('occupants.privacy.allow_profile_after_confirmed_booking') }}" />
            </div>
        </flux:card>

        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" icon="check">
            <span wire:loading.remove>{{ __('occupants.actions.save_privacy') }}</span>
            <span wire:loading>{{ __('occupants.actions.saving') }}</span>
        </flux:button>
    </form>
</div>
